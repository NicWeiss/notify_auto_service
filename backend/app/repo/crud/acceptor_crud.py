from app.repo.models.acceptor_model import Acceptor
from app.repo.crud.base_crud import Crud


class AcceptorCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=Acceptor)
