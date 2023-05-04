from string import Template


html = '''
<html><head></head><body><center style="width:100%">
  <table role="presentation" border="0" class="m_-6475456181520620367phoenix-email-container" cellspacing="0"
  cellpadding="0" width="512"
  bgcolor="#FFFFFF" style="background-color:#ffffff;margin:0 auto;max-width:512px;width:inherit">
    <tbody>
      <tr>
        <td bgcolor="#de6c20" style="background-color:#de6c20;padding:12px;border-bottom:1px solid #ececec">
          <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%"
          style="width:100%!important;min-width:100%!important">
            <tbody>
              <tr>
                <td valign="middle" width="100%" align="right">
                  <a style="margin:0;color:#0073b1;display:inline-block;text-decoration:none" target="_blank">
                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%">
                      <tbody>
                        <tr>
                          <td align="right" valign="middle" style="padding:0 0 0 10px;text-align:right">
                            <p style="margin:0;font-weight:400">
                              <span style="word-wrap:break-word;color:#ffffff;word-break:break-word;font-weight:900;
                                           font-size:14px;line-height:1.429">
                                NOTIFIER
                               </span>
                            </p>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </a>
                </td>
                <td width="1">&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%">
            <tbody>
              <tr>
                <td style="padding:20px 24px 10px 24px">
                  <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%">
                    <tbody>
                      <tr>
                        <td style="padding-bottom:20px"></td>
                      </tr>
                      <tr>
                        <td style="padding-bottom:20px">
                          <p style="margin:0;color:#4c4c4c;font-weight:400;font-size:16px;line-height:1.25">
                          Перейдите по ссылке для восстановления пароля</p>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding-bottom:20px">
                          <h2 style="margin:0;color:#262626;font-weight:700;font-size:24px;line-height:1.167">
                            <a href="${protocol}://${domain}/auth/restore/${reg_code}/password">
                            ${protocol}://${domain}/auth/restore/${reg_code}/password
                            </a>
                          </h2>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding-bottom:20px">

                          <p style="margin:0;color:#4c4c4c;font-weight:400;font-size:16px;line-height:1.25">
                          Если вы не запрашивали восстановление пароля, то просто проигнорируйте письмо</p>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
</center>
</body></html>
'''


def get_template_for_restore_link(protocol, domain, reg_code):
    return Template(html).substitute(protocol=protocol, domain=domain, reg_code=reg_code)
