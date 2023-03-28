from app.repo.models.user import User
from app.repo.crud.base import Crud

from sqlalchemy import and_


class UserCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=User)

    def get_by_email(self, email: str) -> User:
        return self.db.query(self.model).filter(self.model.email == email).first()

    def get_by_password_and_email(self, password: str, email: str) -> User:
        return self.db.query(self.model).filter(and_(
            self.model.password == password,
            self.model.email == email
        )).first()
