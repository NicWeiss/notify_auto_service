// Package mail is a minimal SMTP client. Like the former python client it
// talks plain SMTP without authentication: it is used with the local postfix.
package mail

import (
	"bytes"
	"crypto/rand"
	"encoding/hex"
	"errors"
	"fmt"
	"html"
	"mime"
	"mime/multipart"
	"mime/quotedprintable"
	"net"
	"net/mail"
	"net/smtp"
	"net/textproto"
	"os"
	"regexp"
	"strings"
	"time"
)

type Address struct {
	Name  string
	Email string
}

type Client struct {
	host string
	port string
}

// NewClient parses a dsn like [user:password@]host:port. Credentials are
// ignored, as before.
func NewClient(dsn string) (*Client, error) {
	if dsn == "" {
		return nil, fmt.Errorf("%w: SMTP_URI", ErrNotConfigured)
	}

	if at := strings.LastIndex(dsn, "@"); at >= 0 {
		dsn = dsn[at+1:]
	}

	host, port, err := net.SplitHostPort(dsn)
	if err != nil {
		return nil, fmt.Errorf("bad SMTP_URI: %w", err)
	}

	return &Client{host: host, port: port}, nil
}

// ErrNotConfigured: the smtp server or the sender address is not set.
var ErrNotConfigured = errors.New("mail is not configured")

const (
	maxMessagesPerConnection = 200
	connectionIdle           = 20 * time.Second
	operationTimeout         = 30 * time.Second
)

// Send sends one message over a fresh connection. Use a Session to send many.
func (c *Client) Send(from Address, to []string, subject string, text string, htmlBody string) error {
	session := c.NewSession()
	defer session.Close()

	return session.Send(from, to, subject, text, htmlBody)
}

// Session keeps an smtp connection open between messages, so a worker does
// not pay for the connect and EHLO of every letter. It is not safe for
// concurrent use: every worker has its own session.
type Session struct {
	client  *Client
	network net.Conn
	smtp    *smtp.Client
	used    int
	lastUse time.Time
}

func (c *Client) NewSession() *Session {
	return &Session{client: c}
}

func (s *Session) connect() error {
	network, err := net.DialTimeout("tcp", net.JoinHostPort(s.client.host, s.client.port), 10*time.Second)
	if err != nil {
		return err
	}
	network.SetDeadline(time.Now().Add(operationTimeout))

	connection, err := smtp.NewClient(network, s.client.host)
	if err != nil {
		network.Close()
		return err
	}

	if hostname, err := os.Hostname(); err == nil {
		if err = connection.Hello(hostname); err != nil {
			connection.Close()
			return err
		}
	}

	s.network, s.smtp, s.used = network, connection, 0
	return nil
}

// ready gives a live connection: the old one if it is fresh enough.
func (s *Session) ready() error {
	if s.smtp != nil {
		idle := time.Since(s.lastUse)

		switch {
		case s.used >= maxMessagesPerConnection || idle > connectionIdle:
			s.Close()
		case idle > 2*time.Second:
			// the server could have dropped a quiet connection
			s.network.SetDeadline(time.Now().Add(operationTimeout))
			if s.smtp.Noop() != nil {
				s.Close()
			}
		}
	}

	if s.smtp == nil {
		return s.connect()
	}

	return nil
}

func (s *Session) Send(from Address, to []string, subject string, text string, htmlBody string) error {
	if from.Email == "" {
		return fmt.Errorf("%w: sender address", ErrNotConfigured)
	}
	if len(to) == 0 {
		return errors.New("no recipients")
	}
	for _, recipient := range to {
		if _, err := mail.ParseAddress(recipient); err != nil {
			return &AddressError{Address: recipient, Err: err}
		}
	}

	message, err := BuildMessage(from, to, subject, text, htmlBody, time.Now())
	if err != nil {
		return err
	}

	if err = s.ready(); err != nil {
		return err
	}
	s.network.SetDeadline(time.Now().Add(operationTimeout))

	if err = s.deliver(from, to, message); err != nil {
		var protocolError *textproto.Error
		if errors.As(err, &protocolError) && s.smtp.Reset() == nil {
			// the server has refused the message, but the connection is fine
			return err
		}

		s.Close()
		return err
	}

	s.used++
	s.lastUse = time.Now()
	return nil
}

func (s *Session) deliver(from Address, to []string, message []byte) error {
	if err := s.smtp.Mail(from.Email); err != nil {
		return err
	}
	for _, recipient := range to {
		if err := s.smtp.Rcpt(recipient); err != nil {
			return err
		}
	}

	writer, err := s.smtp.Data()
	if err != nil {
		return err
	}
	if _, err = writer.Write(message); err != nil {
		writer.Close()
		return err
	}

	return writer.Close()
}

func (s *Session) Close() {
	if s.smtp == nil {
		return
	}

	s.network.SetDeadline(time.Now().Add(2 * time.Second))
	if s.smtp.Quit() != nil {
		s.smtp.Close()
	}
	s.smtp, s.network = nil, nil
}

// AddressError: the recipient address can never be delivered.
type AddressError struct {
	Address string
	Err     error
}

func (e *AddressError) Error() string {
	return fmt.Sprintf("bad address %q: %v", e.Address, e.Err)
}

func (e *AddressError) Unwrap() error {
	return e.Err
}

// IsPermanent tells that a retry makes no sense: the server has refused the
// message (5xx), the address is wrong or the mail is not configured.
func IsPermanent(err error) bool {
	var protocolError *textproto.Error
	var addressError *AddressError

	return errors.Is(err, ErrNotConfigured) ||
		errors.As(err, &addressError) ||
		(errors.As(err, &protocolError) && protocolError.Code >= 500)
}

// BuildMessage creates a utf-8 message. When html is given it is sent as an
// alternative to the text; a missing text is made from the html.
func BuildMessage(from Address, to []string, subject string, text string, htmlBody string, now time.Time) ([]byte, error) {
	if text == "" && htmlBody == "" {
		text = " "
	}
	if text == "" {
		text = HtmlToText(htmlBody)
	}

	recipients := make([]string, 0, len(to))
	for _, recipient := range to {
		recipients = append(recipients, (&mail.Address{Address: recipient}).String())
	}

	hostname, _ := os.Hostname()
	if hostname == "" {
		hostname = "localhost"
	}
	randomPart := make([]byte, 6)
	rand.Read(randomPart)

	var body bytes.Buffer
	header := func(key string, value string) {
		value = strings.NewReplacer("\r", " ", "\n", " ").Replace(value)
		fmt.Fprintf(&body, "%s: %s\r\n", key, value)
	}

	header("Message-ID", fmt.Sprintf("<%d.%s@%s>", now.UnixNano(), hex.EncodeToString(randomPart), hostname))
	header("Subject", mime.QEncoding.Encode("utf-8", subject))
	header("From", (&mail.Address{Name: from.Name, Address: from.Email}).String())
	header("To", strings.Join(recipients, ", "))
	header("Date", now.UTC().Format(time.RFC1123Z))
	header("MIME-Version", "1.0")

	if htmlBody == "" {
		header("Content-Type", `text/plain; charset="utf-8"`)
		header("Content-Transfer-Encoding", "quoted-printable")
		body.WriteString("\r\n")
		if err := writeQuotedPrintable(&body, text); err != nil {
			return nil, err
		}

		return body.Bytes(), nil
	}

	writer := multipart.NewWriter(&body)
	header("Content-Type", fmt.Sprintf(`multipart/alternative; boundary="%s"`, writer.Boundary()))
	body.WriteString("\r\n")

	for _, part := range []struct{ contentType, content string }{
		{`text/plain; charset="utf-8"`, text},
		{`text/html; charset="utf-8"`, htmlBody},
	} {
		partHeader := textproto.MIMEHeader{}
		partHeader.Set("Content-Type", part.contentType)
		partHeader.Set("Content-Transfer-Encoding", "quoted-printable")

		partWriter, err := writer.CreatePart(partHeader)
		if err != nil {
			return nil, err
		}
		if err = writeQuotedPrintable(partWriter, part.content); err != nil {
			return nil, err
		}
	}

	if err := writer.Close(); err != nil {
		return nil, err
	}

	return body.Bytes(), nil
}

func writeQuotedPrintable(destination interface{ Write([]byte) (int, error) }, content string) error {
	writer := quotedprintable.NewWriter(destination)
	if _, err := writer.Write([]byte(content)); err != nil {
		return err
	}

	return writer.Close()
}

var (
	tagPattern        = regexp.MustCompile(`(?s)<(script|style)[^>]*>.*?</(script|style)>|<[^>]+>`)
	blankLinesPattern = regexp.MustCompile(`\n\s*\n+`)
	spacesPattern     = regexp.MustCompile(`[ \t\r\f\v]+`)
)

// HtmlToText is a simple replacement of `lynx -dump`.
func HtmlToText(source string) string {
	text := strings.NewReplacer("</p>", "\n", "</tr>", "\n", "</h2>", "\n", "<br>", "\n", "<br/>", "\n").Replace(source)
	text = tagPattern.ReplaceAllString(text, " ")
	text = html.UnescapeString(text)
	text = spacesPattern.ReplaceAllString(text, " ")

	lines := strings.Split(text, "\n")
	for i := range lines {
		lines[i] = strings.TrimSpace(lines[i])
	}

	return strings.TrimSpace(blankLinesPattern.ReplaceAllString(strings.Join(lines, "\n"), "\n\n"))
}
