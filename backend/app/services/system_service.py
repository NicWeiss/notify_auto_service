from sqlalchemy.orm import Session

from app.repo.crud.system_crud import SystemCrud
from app.services import ServiceResponse


class SystemService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.system_crud = SystemCrud(db=db)

    def get_list(self) -> ServiceResponse:
        systems_models = self.system_crud.get_all_public()

        return ServiceResponse(data=systems_models)

    def get_by_id(self, system_id: int) -> ServiceResponse:
        systems_models = self.system_crud.get_by_id(id=system_id)

        return ServiceResponse(data=systems_models)
