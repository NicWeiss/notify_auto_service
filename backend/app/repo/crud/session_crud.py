from arrow import Arrow
from sqlalchemy import and_

from app.repo.models.session_model import Session
from app.repo.crud.base_crud import Crud


class SessionCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=Session)

    def get_by_session(self, session: str) -> Session:
        return self.db.query(self.model).filter(and_(
            self.model.session == session,
            self.model.expire_at > Arrow.now()
        )).first()

    def clear_expires_sessions(self):
        sessions = self.db.query(self.model).filter(self.model.expire_at < Arrow.now())
        sessions.delete(synchronize_session=False)
