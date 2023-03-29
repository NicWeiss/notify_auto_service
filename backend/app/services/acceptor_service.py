from sqlalchemy.orm import Session

from app.repo.crud.acceptor_crud import AcceptorCrud
from app.services import ServiceResponse


class AcceptorService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.acceptor_crud = AcceptorCrud(db=db)

    def get_by_user_id(self, user_id: int) -> ServiceResponse:
        acceptors_models = self.acceptor_crud.get_all_by_user_id(user_id=user_id)

        return ServiceResponse(data=acceptors_models)
