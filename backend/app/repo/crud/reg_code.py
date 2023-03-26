from app.repo.models.reg_code import RegCode
from app.repo.crud.base import Crud


class RegCodeCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=RegCode)
