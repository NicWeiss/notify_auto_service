from typing import List

from sqlalchemy import and_

from app.repo.models.system_model import System
from app.repo.crud.base_crud import Crud


class SystemCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=System)

    def get_all(self) -> List[System]:
        return self.db.query(self.model).filter(and_(
            self.model.is_disabled.is_(False),
            self.model.is_deleted.is_(False)
        )).all()

    def get_by_type(self, type: str) -> System:
        return self.db.query(self.model).filter(and_(
            self.model.type == type,
            self.model.is_deleted.is_(False)
        )).first()
