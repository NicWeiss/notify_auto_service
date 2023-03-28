from sqlalchemy.orm import Session

from app.repo.crud.category import CategoryCrud
from app.services import ServiceResponse


class CategoryService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.category_crud = CategoryCrud(db=db)

    def get_by_user_id(self, user_id: int) -> ServiceResponse:
        category_models = self.category_crud.get_all_by_user_id(user_id=user_id)

        return ServiceResponse(data=category_models)
