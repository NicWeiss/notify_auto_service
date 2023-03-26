from app.repo.models.user import User
from app.repo.crud.base import Crud


class UserCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=User)
