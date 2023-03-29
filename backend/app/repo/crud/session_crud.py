from app.repo.models.session_model import Session
from app.repo.crud.base_crud import Crud


class SessionCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=Session)

    def get_by_session(self, session: str) -> Session:
        return self.db.query(self.model).filter(self.model.session == session).first()
