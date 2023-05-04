from typing import Optional, List

from sqlalchemy.orm import Session

from app.repo.crud.notify_acceptor_crud import NotifyAcceptorCrud
from app.repo.crud.notify_crud import NotifyCrud
from app.services import ServiceResponse
from app.repo.schemas.notify_acceptor_scheme import NotifyAcceptorCreateScheme
from app.repo.schemas.notify_scheme import NotifyCreateScheme, NotifyUpdateScheme


class NotifyService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.notify_crud = NotifyCrud(db=db)
        self.notify_acceptor_crud = NotifyAcceptorCrud(db=db)

    def _delete_notify_acceptors(self, notify_id: int) -> None:
        notify_acceptor_models = self.notify_acceptor_crud.get_all_by_notify_id(notify_id=notify_id)

        for notify_acceptor_model in notify_acceptor_models:
            self.notify_acceptor_crud.remove_db_object(notify_acceptor_model)

    def _recreate_notify_acceptors(self, notify_id: int, new_acceptors: List):
        self._delete_notify_acceptors(notify_id=notify_id)

        for acceptor in new_acceptors:
            scheme_object = NotifyAcceptorCreateScheme(notify_id=notify_id, acceptor_id=acceptor['id'])
            self.notify_acceptor_crud.create(scheme=scheme_object)

    def get(self, user_id: int, notify_id: int) -> ServiceResponse:
        notify = self.notify_crud.get_by_id_and_user_id(user_id=user_id, id=notify_id)

        return ServiceResponse(data=notify)

    def get_list(
        self,
        user_id: int,
        category_id: Optional[int],
        page: Optional[int],
        per_page: Optional[int]
    ) -> ServiceResponse:
        notify_models = self.notify_crud.get_list(
            user_id=user_id,
            category_id=category_id,
            page=page,
            per_page=per_page
        )

        meta = self.notify_crud.get_meta(user_id=user_id, per_page=per_page, category_id=category_id)

        return ServiceResponse(data=notify_models, meta=meta)

    def reset_category(self, user_id: int, category_id: int) -> ServiceResponse:
        notifies = self.notify_crud.get_all_by_user_id_and_category_id(user_id=user_id, category_id=category_id)
        scheme_object = NotifyUpdateScheme(category_id=None)

        for notify in notifies:
            self.notify_crud.update(db_object=notify, scheme=scheme_object)

        return ServiceResponse()

    def delete_by_category(self, user_id: int, category_id: int) -> ServiceResponse:
        notifies = self.notify_crud.get_all_by_user_id_and_category_id(user_id=user_id, category_id=category_id)

        for notify in notifies:
            self.notify_crud.delete_db_object(db_object=notify)

        return ServiceResponse()

    def create(self, **kwargs):
        acceptors = kwargs.pop('acceptors', [])

        notify_create_scheme = NotifyCreateScheme(**kwargs)
        notify_model = self.notify_crud.create(scheme=notify_create_scheme)
        self._recreate_notify_acceptors(notify_id=notify_model.id, new_acceptors=acceptors)

        notify_model = self.notify_crud.get_by_id(id=notify_model.id)

        return ServiceResponse(data=notify_model)

    def update(self, user_id: int, notify_id: int, **kwargs):
        notify_model = self.notify_crud.get_by_id_and_user_id(id=notify_id, user_id=user_id)

        if not notify_model:
            return ServiceResponse(is_error=True, description=f'Notify with id {notify_id} not found')

        acceptors = kwargs.pop('acceptors', [])
        scheme_object = NotifyUpdateScheme(**kwargs)
        notify_model = self.notify_crud.update(db_object=notify_model, scheme=scheme_object)
        self._recreate_notify_acceptors(notify_id=notify_model.id, new_acceptors=acceptors)

        notify_model = self.notify_crud.get_by_id(id=notify_model.id)

        return ServiceResponse(data=notify_model)

    def delete(self, user_id: int, notify_id: int) -> ServiceResponse:
        notify_model = self.notify_crud.get_by_id_and_user_id(id=notify_id, user_id=user_id)

        if not notify_model:
            return ServiceResponse(is_error=True, description=f'Notify with id {notify_id} not found')

        self._delete_notify_acceptors(notify_id=notify_id)
        self.notify_crud.delete_db_object(db_object=notify_model)

        return ServiceResponse()
