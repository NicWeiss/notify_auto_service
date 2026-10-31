package templates

import (
	_ "embed"
	"html"
	"strings"
)

//go:embed reg_code.html
var regCodeHtml string

//go:embed restore_link.html
var restoreLinkHtml string

func RegCodeEmail(code string) string {
	return strings.NewReplacer("${reg_code}", html.EscapeString(code)).Replace(regCodeHtml)
}

func RestoreLinkEmail(protocol string, domain string, code string) string {
	return strings.NewReplacer(
		"${protocol}", html.EscapeString(protocol),
		"${domain}", html.EscapeString(domain),
		"${reg_code}", html.EscapeString(code),
	).Replace(restoreLinkHtml)
}
