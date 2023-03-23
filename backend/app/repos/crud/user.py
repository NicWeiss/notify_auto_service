from app.repos.models.user import User
from app.repos.crud.base import Crud


class UserCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=User)
