# from app.repo.crud.user import UserCrud
import random
from datetime import timedelta

from arrow import Arrow
from sqlalchemy.orm import Session

from app.core.config import settings
from app.repo.crud.reg_code import RegCodeCrud
from app.repo.schemas.reg_code_scheme import RegCodeScheme
from app.services.email import EmailService
from app.templates.reg_code import get_template_for_reg_code


class AuthService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.reg_code_crud = RegCodeCrud(db=db)

    def send_verify_code_to_email(self, email: str) -> None:
        code = random.randint(1000, 9999)
        template = get_template_for_reg_code(reg_code=str(code))

        scheme = RegCodeScheme(code=code, email=email, expire_at=Arrow.now() + timedelta(minutes=3))
        self.reg_code_crud.create(scheme=scheme)

        email_service = EmailService()
        email_service.create(subject="Registration code", recipients=[email],
                             sender=settings.NOTIFIER_SENDER, text='', html=template)
        email_service.send()
