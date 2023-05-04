from typing import List

from sqlalchemy.orm import Session

from app.repo.crud.acceptor_crud import AcceptorCrud
from app.repo.crud.system_crud import SystemCrud
from app.repo.schemas.acceptor_scheme import AcceptorCreateScheme, AcceptorUpdateScheme
from app.services import ServiceResponse


class FcmService:
    def __init__(self, db: Session, user_id: int):
        self.acceptor_crud = AcceptorCrud(db=db)
        self.system_crud = SystemCrud(db=db)

        self.user_id = user_id
        self.push_system_id = self.system_crud.get_by_type(type='push').id
        self.acceptor = self.acceptor_crud.get_by_user_id_and_system_id(user_id=user_id, system_id=self.push_system_id)

    def _get_tokens(self):
        fcm_tokens = []

        if self.acceptor:
            if self.acceptor.account:
                fcm_tokens = self.acceptor.account.split(';')

        return fcm_tokens

    def set_tokens(self, fcm_tokens: List) -> ServiceResponse:
        account_string = ';'.join(fcm_tokens)

        if self.acceptor:
            acceptor_scheme = AcceptorUpdateScheme(account=account_string)
            self.acceptor_crud.update(db_object=self.acceptor, scheme=acceptor_scheme)
        else:
            acceptor_scheme = AcceptorCreateScheme(
                user_id=self.user_id,
                name='Push',
                system_id=self.push_system_id,
                is_system=True,
                account=account_string,
                is_disabled=False
            )
            self.acceptor = self.acceptor_crud.create(scheme=acceptor_scheme)

        return ServiceResponse()

    def add_fcm_token(self, token: str) -> ServiceResponse:
        fcm_tokens = self._get_tokens()

        if token not in fcm_tokens:
            fcm_tokens.append(token)

        self.set_tokens(fcm_tokens)

        return ServiceResponse()

    def remove_fcm_token(self, token: str) -> ServiceResponse:
        fcm_tokens = self._get_tokens()
        to_remove = [token]
        new_fcm_tokens = list(set(fcm_tokens) - set(to_remove))

        self.set_tokens(new_fcm_tokens)

        return ServiceResponse()
