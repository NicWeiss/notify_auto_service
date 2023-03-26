import itertools
import os
import random
import smtplib
import socket
import ssl
import time
from email import encoders
from email.header import make_header
from email.mime.audio import MIMEAudio
from email.mime.base import MIMEBase
from email.mime.image import MIMEImage
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

from app.utils import helper as h


class SMTPException(Exception):
    pass


class FQDN:
    def __init__(self):
        self.__fqdn = None

    def __str__(self):
        if self.__fqdn is None:
            self.__fqdn = socket.getfqdn()
        return self.__fqdn


FQDN = FQDN()


class SMTPClient:
    """
    :param str dsn: Строка вида ``<user>:<password>@<host>:<port>``
    :param tuple from_addr: Адрес отправителя. Кортеж из двух элементов -- Имя и E-Mail.
    :param str local_hostname: FQDN, которое используется в команде HELO/EHLO для отправки почты
    :param bool debug: Включить/выключить отладочный режим
    """

    def __init__(self, dsn, from_addr=None, local_hostname=None, debug=False):
        dsn = list(itertools.chain(*map(lambda i: i.split(':'), dsn.split('@'))))
        dsn_len = len(dsn)
        if dsn_len == 2:
            self.__host, self.__port = dsn
            self.__user, self.__passwd = None, None
        elif dsn_len == 4:
            self.__user, self.__passwd, self.__host, self.__port = dsn
        else:
            raise ValueError('Bad DSN')
        self.__port = int(self.__port)
        self.__local_hostname = local_hostname
        self.__from_addr = from_addr
        self.__debug = debug
        self.__tls = False
        self.__ssl = False
        self.__ssl_ctx = None

    def use_tls(self, enabled=True):
        self.__tls = enabled

    def use_ssl(self, enabled=True):
        self.__ssl = enabled
        if enabled:
            self.__ssl_ctx = ssl.SSLContext(ssl.PROTOCOL_SSLv3)
        else:
            self.__ssl_ctx = None

    def send(self, **kwargs):
        if 'from_' not in kwargs:
            kwargs['from_'] = self.__from_addr
        msg = create_message(**kwargs)
        connection = smtplib.SMTP(
            host=self.__host,
            port=self.__port,
            local_hostname=self.__local_hostname
        )
        connection.set_debuglevel(self.__debug)
        if self.__tls or self.__ssl:
            connection.ehlo()
            connection.starttls(context=self.__ssl_ctx)
            connection.ehlo()
        # if self.__user and self.__passwd:
        #     connection.login(self.__user, self.__passwd)
        errors = connection.send_message(msg)
        connection.quit()
        if errors:
            raise SMTPException(errors)
        return True

    @classmethod
    def from_config(cls, config, prefix='mailer.'):
        config = dict(
            (key[len(prefix):], config[key])
            for key in config if key.startswith(prefix)
        )

        def asbool(value):
            if not isinstance(value, bool):
                value = value in ('1', 'on', 'true')
            return value

        config['from_addr'] = (config.pop('from.name'), config.pop('from.address'))
        config['debug'] = asbool(config.get('debug', False))
        use_tls = asbool(config.pop('use_tls', False))
        use_ssl = asbool(config.pop('use_ssl', False))

        client = cls(**config)
        client.use_tls(use_tls)
        client.use_ssl(use_ssl)
        return client


def create_message(subject, from_, to, **kwargs):
    attachments = kwargs.get('attachments', None)
    charset = kwargs.get('charset', 'utf-8')
    text = kwargs.get('text', None)
    html = kwargs.get('html', None)

    if not text and not html:
        raise ValueError('Empty message')

    text = h.html_to_text(html) if html and not text else text

    if attachments:
        msg = create_multipart_message(text, html, attachments, charset)
    else:
        msg = create_plain_message(text, html, charset)

    msg['Message-id'] = make_message_id_header()

    if charset != 'us-ascii':
        subject = str(make_header([(subject, charset)]))
    msg['Subject'] = subject

    msg['From'] = make_from_header(from_, charset)

    if 'cc' in kwargs:
        if isinstance(kwargs['cc'], list):
            msg['CC'] = ', '.join(kwargs['cc'])
        else:
            msg['CC'] = kwargs['cc']

    if isinstance(to, list):
        msg['To'] = make_to_header(to, charset)
    else:
        raise TypeError('Invalid recipient format')

    msg['Date'] = kwargs.get('date', time.strftime("%a, %d %b %Y %H:%M:%S %z", time.gmtime()))

    if 'headers' in kwargs:
        for key, value in kwargs['headers'].items():
            msg[key] = value

    return msg


def create_plain_message(text, html, charset):
    if html:
        msg = MIMEMultipart('alternative')
        msg.attach(MIMEText(text, 'plain', charset))
        msg.attach(MIMEText(html, 'html', charset))
    else:
        msg = MIMEText(text, 'plain', charset)
    return msg


def create_multipart_message(text, html, attachments, charset):
    msg = MIMEMultipart('related')

    if html:
        body = MIMEMultipart('alternative')

        text = MIMEText(text, 'plain', charset)
        text.add_header('Content-Disposition', 'inline')
        body.attach(text)

        html = MIMEText(html, 'html', charset)
        html.add_header('Content-Disposition', 'inline')
        body.attach(html)
    else:
        body = MIMEText(text, 'plain', charset)
    msg.attach(body)

    for item in attachments:
        if isinstance(item, str):
            item = (item, )
        msg.attach(make_attachment(*item))

    return msg


def make_message_id_header():
    """Returns a string suitable for RFC 2822 compliant Message-ID, e.g:

    <20020201195627.33539.96671@nightshade.la.mastaler.com>

    Optional idstring if given is a string used to strengthen the
    uniqueness of the message id.

    Copied from Python standard library, with the following modifications:

        - Used cached hostname for performance.
        - Added try/except to support lack of getpid() in Jython.

    https://github.com/django/django/blob/1.8.5/django/core/mail/message.py#L42
    """
    timeval = time.time()
    utcdate = time.strftime('%Y%m%d%H%M%S', time.gmtime(timeval))
    try:
        pid = os.getpid()
    except AttributeError:
        # No getpid() in Jython, for example.
        pid = 1
    randint = random.randrange(100000)
    msgid = '<%s.%s.%s@%s>' % (utcdate, pid, randint, FQDN)
    return msgid


def make_from_header(from_, charset):
    if not isinstance(from_, tuple):
        raise TypeError(
            'Invalid type of "from_" argument: '
            'Expects tuple, {0} given'.format(type(from_).__name__)
        )
    name, email = from_
    if not name:
        return email
    return make_header([(name, charset), ('<{0}>'.format(email), 'us-ascii')]).encode()


def make_to_header(to_, charset):
    '''Метод формирования заголовка сообщения для получателя
    Входные параметры:
        to_: получатель, представлен в следующем формате
            - [('Name', 'Email')]
            - ['Email']
        charset: кодировка
    Результат:
        email получателя.
        Пример:
            'test@test.com'
            '=?utf-8?q?Test?= <test@test.com>'
    '''
    to_ = to_.pop()
    if isinstance(to_, str):
        return to_
    elif isinstance(to_, tuple):
        name, email = to_

        if not name:
            return email

        return make_header([(name, charset), ('<{0}>'.format(email), 'us-ascii')]).encode()


def make_attachment(bfile, filename, content_id=None, mimetype=None):
    if mimetype:
        content_type = mimetype

    if content_type is None:
        content_type = 'application/octet-stream'

    main_type, sub_type = content_type.split('/', 1)

    cls = None
    data = bfile.read()

    if main_type == 'text':
        cls = MIMEText
        data = data.decode('utf-8')
    elif main_type == 'image':
        cls = MIMEImage
    elif main_type == 'audio':
        cls = MIMEAudio
    if not cls:
        msg = MIMEBase(main_type, sub_type)
        msg.set_payload(data)
        encoders.encode_base64(msg)
    else:
        msg = cls(data, _subtype=sub_type)

    if content_id:
        msg.add_header('Content-ID', '<%s>' % content_id)
        msg.add_header('Content-Disposition', 'inline')
    else:
        msg.add_header('Content-Disposition', 'attachment',
                       filename=filename)

    return msg
