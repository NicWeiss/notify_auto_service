from typing import Dict, List

from app.core.config import settings
from app.utils.smtp_client import SMTPClient


class Email:
    account_id: int = None
    subject: str = None
    text: str = None
    html: str = None
    recipients = List
    sender: Dict = None

    def __init__(self, **kw):
        for key, value in kw.items():
            if hasattr(self, key):
                setattr(self, key, value)


class EmailService:
    def __init__(self) -> None:
        self.email = None
        self.smtp_client = SMTPClient(dsn=settings.SMTP_URI)

    def create(
        self,
        subject: str,
        recipients: List,
        text: str = '',
        html: str = None,
        sender: Dict = settings.NOTIFIER_SENDER
    ):
        ''' Создание письма
        '''
        self.email = Email(subject=subject, text=text, recipients=recipients, html=html, sender=sender)

    def send(self):
        ''' Отправка письма
        '''

        recipients = []
        for recipient in self.email.recipients:
            if isinstance(recipient, str):
                recipients.append(recipient)
            elif recipient.get('name'):
                recipients.append((recipient['name'], recipient['email']))
            else:
                recipients.append(recipient['email'])

        sender = self.email.sender.get('name'), self.email.sender['email']

        is_sent = self.smtp_client.send(
            to=recipients, from_=sender,
            subject=self.email.subject, text=self.email.text, html=self.email.html
        )

        if is_sent:
            return True
        else:
            return False
