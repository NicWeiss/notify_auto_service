from sqlalchemy.orm import Session

from app.repo.crud.acceptor_crud import AcceptorCrud
from app.repo.crud.notify_acceptor_crud import NotifyAcceptorCrud
from app.repo.schemas.acceptor_scheme import AcceptorCreateScheme, AcceptorUpdateScheme
from app.services import ServiceResponse
from app.services.fcm_service import FcmService


class AcceptorService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.acceptor_crud = AcceptorCrud(db=db)
        self.notify_acceptor_crud = NotifyAcceptorCrud(db=db)

    def get_by_user_id(self, user_id: int, only_enabled: bool = False) -> ServiceResponse:
        acceptors_models = self.acceptor_crud.get_by_user_id(user_id=user_id, only_enabled=only_enabled)

        return ServiceResponse(data=acceptors_models)

    def create(self, **kwargs):
        acceptor_create_scheme = AcceptorCreateScheme(**kwargs)
        acceptor_model = self.acceptor_crud.create(scheme=acceptor_create_scheme)

        return ServiceResponse(data=acceptor_model)

    def update(self, user_id: int, acceptor_id: int, **kwargs):
        acceptor_model = self.acceptor_crud.get_by_id_and_user_id(id=acceptor_id, user_id=user_id)

        if not acceptor_model:
            return ServiceResponse(is_error=True, description=f'Acceptor with id {acceptor_id} not found')

        scheme_object = AcceptorUpdateScheme(**kwargs)
        acceptor_model = self.acceptor_crud.update(db_object=acceptor_model, scheme=scheme_object)

        return ServiceResponse(data=acceptor_model)

    def delete(self, user_id: int, acceptor_id: int) -> ServiceResponse:
        acceptor_model = self.acceptor_crud.get_by_id_and_user_id(id=acceptor_id, user_id=user_id)

        if not acceptor_model:
            return ServiceResponse(is_error=True, description=f'Acceptor with id {acceptor_id} not found')

        notify_acceptor_models = self.notify_acceptor_crud.get_all_by_acceptor_id(acceptor_id=acceptor_id)

        for notify_acceptor_model in notify_acceptor_models:
            self.notify_acceptor_crud.remove_db_object(notify_acceptor_model)

        self.acceptor_crud.delete_db_object(db_object=acceptor_model)

        return ServiceResponse()

    def update_push_tokens(self, user_id: str, token: str):
        fcm_service = FcmService(db=self.db, user_id=user_id)
        fcm_service.add_fcm_token(token=token)

        return ServiceResponse()
