from arrow import Arrow
from sqlalchemy import and_

from app.repo.models.reg_code_model import RegCode
from app.repo.crud.base_crud import Crud


class RegCodeCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=RegCode)

    def get_actual_code_by_email(self, email: str) -> RegCode:
        return self.db.query(self.model).filter(and_(
            self.model.email == email,
            self.model.expire_at > Arrow.now(),
        )).first()

    def get_actual_code_by_code_and_email(self, code: str, email: str) -> RegCode:
        return self.db.query(self.model).filter(and_(
            self.model.email == email,
            self.model.code == code,
            self.model.expire_at > Arrow.now(),
        )).first()

    def get_any_code_by_code_and_email(self, code: str, email: str) -> RegCode:
        return self.db.query(self.model).filter(and_(
            self.model.email == email,
            self.model.code == code
        )).first()

    def remove_used_code(self, code: str, email: str) -> bool:
        reg_code = self.get_any_code_by_code_and_email(code=code, email=email)

        return self.remove_by_id(id=reg_code.id)

    def get_by_code(self, code: str) -> RegCode:
        return self.db.query(self.model).filter(and_(
            self.model.code == code,
            self.model.expire_at > Arrow.now(),
            self.model.is_deleted.is_(False)
        )).first()
