from typing import List

from sqlalchemy import and_

from app.repo.models.notify_acceptor_model import NotifyAcceptor
from app.repo.crud.base_crud import Crud


class NotifyAcceptorCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=NotifyAcceptor)

    def get_all_by_acceptor_id(self, acceptor_id: int) -> List[NotifyAcceptor]:
        return self.db.query(self.model).filter(and_(
            self.model.acceptor_id == acceptor_id,
            self.model.is_deleted.is_(False)
        )).all()

    def get_all_by_notify_id(self, notify_id: int) -> List[NotifyAcceptor]:
        return self.db.query(self.model).filter(and_(
            self.model.notify_id == notify_id,
            self.model.is_deleted.is_(False)
        )).all()
