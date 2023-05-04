import hashlib

from typing import Dict

from arrow import Arrow
from sqlalchemy.orm import Session

from app.repo.crud.user_crud import UserCrud
from app.repo.models.user_model import User
from app.repo.schemas.user_scheme import UserCreateScheme, UserUpdateScheme
from app.services import ServiceResponse


class UserService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.user_crud = UserCrud(db=db)

    def _crypt_password(self, password: str) -> str:
        hash_object = hashlib.sha1(password.encode())

        return hash_object.hexdigest()

    def get_by_id(self, id: int) -> ServiceResponse:
        user_model = self.user_crud.get_by_id(id=id)

        if not user_model:
            return ServiceResponse(is_error=True, description='User not found')

        return ServiceResponse(data=user_model)

    def register_new_user(self, user_data: Dict) -> ServiceResponse:
        data = user_data.dict()
        data['password'] = self._crypt_password(data.get('password', ''))
        user_scheme = UserCreateScheme(**data, registred_at=Arrow.now())
        user_model = self.user_crud.create(scheme=user_scheme)

        return ServiceResponse(data=user_model)

    def get_by_password_and_email(self, password: str, email: str) -> ServiceResponse:
        password = self._crypt_password(password)
        user_model = self.user_crud.get_by_password_and_email(password=password, email=email)

        if not user_model:
            return ServiceResponse(is_error=True, description='User not found')

        return ServiceResponse(data=user_model)

    def change_password_by_code(self, email: str, password: str) -> ServiceResponse:
        user = self.user_crud.get_by_email(email=email)

        if not user:
            return ServiceResponse(is_error=True, description='User not found')

        update_scheme = UserUpdateScheme(password=self._crypt_password(password))
        self.user_crud.update(db_object=user, scheme=update_scheme)

        return ServiceResponse()

    def change_password(self, user: User, current_password: str, new_password: str) -> ServiceResponse:
        if user.password != self._crypt_password(current_password):
            return ServiceResponse(is_error=True, description='Current password is wrong')

        update_scheme = UserUpdateScheme(password=self._crypt_password(new_password))
        self.user_crud.update(db_object=user, scheme=update_scheme)

        return ServiceResponse()

    def update(self, user: User, **kwargs):
        scheme_object = UserUpdateScheme(**kwargs)
        user_model = self.user_crud.update(db_object=user, scheme=scheme_object)

        return ServiceResponse(data=user_model)
