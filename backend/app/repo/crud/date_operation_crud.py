from app.repo.models.date_operation_model import DateOperation
from app.repo.crud.base_crud import Crud


class DateOperationCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=DateOperation)

    def get_last_operation(self) -> DateOperation:
        return self.db.query(self.model).order_by(self.model.id.desc()).first()

    def get_ids_for_process(self):
        return self.db.query(self.model.id).filter(self.model.status == 'new').all()

    def clear_done_operations(self):
        operations = self.db.query(self.model).filter(self.model.status == 'done')
        operations.delete(synchronize_session=False)
