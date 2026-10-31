package mail

import (
	"io"
	"mime"
	"mime/multipart"
	"mime/quotedprintable"
	"net/mail"
	"strings"
	"testing"
	"time"
)

func parse(t *testing.T, raw []byte) *mail.Message {
	t.Helper()

	message, err := mail.ReadMessage(strings.NewReader(string(raw)))
	if err != nil {
		t.Fatal(err)
	}

	return message
}

func TestBuildPlainMessage(t *testing.T) {
	raw, err := BuildMessage(Address{Name: "Нотифаер", Email: "from@example.com"}, []string{"to@example.com"}, "Привет", "Текст письма", "", time.Now())
	if err != nil {
		t.Fatal(err)
	}

	message := parse(t, raw)

	subject, err := new(mime.WordDecoder).DecodeHeader(message.Header.Get("Subject"))
	if err != nil || subject != "Привет" {
		t.Fatalf("subject: %q, %v", subject, err)
	}

	from, err := message.Header.AddressList("From")
	if err != nil || from[0].Name != "Нотифаер" || from[0].Address != "from@example.com" {
		t.Fatalf("from: %v, %v", from, err)
	}

	body, _ := io.ReadAll(quotedprintable.NewReader(message.Body))
	if string(body) != "Текст письма" {
		t.Fatalf("body: %q", body)
	}
}

func TestBuildHtmlMessageHasTextAlternative(t *testing.T) {
	raw, err := BuildMessage(Address{Email: "from@example.com"}, []string{"to@example.com"}, "Code", "", "<p>Code: <b>1234</b></p>", time.Now())
	if err != nil {
		t.Fatal(err)
	}

	message := parse(t, raw)
	mediaType, params, err := mime.ParseMediaType(message.Header.Get("Content-Type"))
	if err != nil || mediaType != "multipart/alternative" {
		t.Fatalf("content type: %q, %v", mediaType, err)
	}

	reader := multipart.NewReader(message.Body, params["boundary"])
	var types, bodies []string
	for {
		part, err := reader.NextPart()
		if err == io.EOF {
			break
		}
		if err != nil {
			t.Fatal(err)
		}

		content, _ := io.ReadAll(quotedprintable.NewReader(part))
		types = append(types, part.Header.Get("Content-Type"))
		bodies = append(bodies, string(content))
	}

	if len(bodies) != 2 || !strings.HasPrefix(types[0], "text/plain") || !strings.HasPrefix(types[1], "text/html") {
		t.Fatalf("parts: %v", types)
	}
	if !strings.Contains(bodies[0], "Code: 1234") {
		t.Fatalf("text part: %q", bodies[0])
	}
}

func TestSubjectCannotInjectHeaders(t *testing.T) {
	raw, err := BuildMessage(Address{Email: "from@example.com"}, []string{"to@example.com"}, "hi\r\nBcc: evil@example.com", "x", "", time.Now())
	if err != nil {
		t.Fatal(err)
	}

	if parse(t, raw).Header.Get("Bcc") != "" {
		t.Fatal("header injection")
	}
}

func TestNewClient(t *testing.T) {
	for dsn, host := range map[string]string{"postfix:25": "postfix", "user:pass@postfix:587": "postfix"} {
		client, err := NewClient(dsn)
		if err != nil || client.host != host {
			t.Fatalf("%s: %v %v", dsn, client, err)
		}
	}

	if _, err := NewClient("no-port"); err == nil {
		t.Fatal("bad dsn accepted")
	}
}
