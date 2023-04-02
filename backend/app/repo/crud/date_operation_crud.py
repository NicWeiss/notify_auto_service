from app.repo.models.date_operation_model import DateOperation
from app.repo.crud.base_crud import Crud


class DateOperationCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=DateOperation)

    def get_list_operation(self) -> DateOperation:
        return self.db.query(self.model).order_by(self.model.id.desc()).first()
