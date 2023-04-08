import random
from datetime import timedelta

from arrow import Arrow
from sqlalchemy.orm import Session

from app.repo.crud.reg_code_crud import RegCodeCrud
from app.repo.crud.user_crud import UserCrud
from app.repo.schemas.reg_code_scheme import RegCodeCreateScheme
from app.services import ServiceResponse
from app.services.email_service import EmailService
from app.templates.reg_code import get_template_for_reg_code


class AuthService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.reg_code_crud = RegCodeCrud(db=db)
        self.user_crud = UserCrud(db=db)

    def send_verify_code_to_email(self, email: str) -> ServiceResponse:
        if self.user_crud.get_by_email(email=email):
            return ServiceResponse(is_error=True, description='Email is busy')

        if self.reg_code_crud.get_actual_code_by_email(email=email):
            return ServiceResponse(is_error=True, description='Code already sent')

        code = random.randint(1000, 9999)
        email_template = get_template_for_reg_code(reg_code=str(code))

        email_service = EmailService()
        email_service.create(subject="Registration code", recipients=[email],
                             text='', html=email_template)
        try:
            email_service.send()
        except Exception:
            return ServiceResponse(is_error=True, description='Error while sending code')

        create_scheme = RegCodeCreateScheme(code=code, email=email, expire_at=Arrow.now() + timedelta(minutes=3))
        self.reg_code_crud.create(scheme=create_scheme)

        return ServiceResponse()

    def verify_code_and_email(self, code: str, email: str) -> ServiceResponse:
        if self.user_crud.get_by_email(email=email):
            return ServiceResponse(is_error=True, description='Email is busy')

        reg_code_model = self.reg_code_crud.get_actual_code_by_code_and_email(code=code, email=email)

        if not reg_code_model:
            return ServiceResponse(is_error=True, description='Code wrong or expired')

        return ServiceResponse()

    def clear_reg_code(self, code: str, email: str) -> ServiceResponse:
        self.reg_code_crud.remove_used_code(code=code, email=email)

        return ServiceResponse()
