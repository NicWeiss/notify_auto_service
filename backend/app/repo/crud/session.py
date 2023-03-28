from app.repo.models.session import Session
from app.repo.crud.base import Crud


class SessionCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=Session)

    def get_by_session(self, session: str) -> Session:
        return self.db.query(self.model).filter(self.model.session == session).first()
