from app.repo.models.session import Session
from app.repo.crud.base import Crud


class SessionCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=Session)
