from typing import List

from sqlalchemy import and_

from app.repo.models.acceptor_model import Acceptor
from app.repo.crud.base_crud import Crud


class AcceptorCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=Acceptor)

    def get_by_user_id(self, user_id: str, only_enabled: bool = False) -> List[Acceptor]:
        query = self.db.query(self.model).filter(
            and_(
                self.model.is_deleted.is_(False),
                self.model.user_id == user_id
            )
        )

        if only_enabled:
            query = query.filter(self.model.is_disabled.is_(False))

        return query.all()
