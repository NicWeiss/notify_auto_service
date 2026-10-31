package email_service

import (
	"notifier/src/core"
	"notifier/src/integrations/mail"
)

// Send sends a letter from the notifier address.
func Send(recipient string, subject string, text string, html string) error {
	client, err := mail.NewClient(core.Cfg.SMTPURI)
	if err != nil {
		return err
	}

	sender := mail.Address{Name: core.Cfg.SenderName, Email: core.Cfg.SenderEmail}

	return client.Send(sender, []string{recipient}, subject, text, html)
}
