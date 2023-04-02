from sqlalchemy.orm import Session

from app.repo.crud.category_crud import CategoryCrud
from app.repo.schemas.category_scheme import CategoryCreateScheme, CategoryUpdateScheme
from app.services import ServiceResponse


class CategoryService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.category_crud = CategoryCrud(db=db)

    def get_by_user_id(self, user_id: int) -> ServiceResponse:
        category_models = self.category_crud.get_all_by_user_id(user_id=user_id)

        return ServiceResponse(data=category_models)

    def create(self, user_id: int, name: str, is_hidden: bool = False) -> ServiceResponse:
        category_create_scheme = CategoryCreateScheme(user_id=user_id, name=name, is_hidden=is_hidden)
        category_model = self.category_crud.create(scheme=category_create_scheme)

        return ServiceResponse(data=category_model)

    def update(self, user_id: int, category_id: int, name: str, is_hidden: bool = False) -> ServiceResponse:
        category_create_scheme = CategoryUpdateScheme(user_id=user_id, name=name, is_hidden=is_hidden)
        category_model = self.category_crud.get_by_id_and_user_id(user_id=user_id, id=category_id)

        if not category_model:
            return ServiceResponse(is_error=True, description=f'Category {category_id} not found')

        updated_category_model = self.category_crud.update(db_object=category_model, scheme=category_create_scheme)

        return ServiceResponse(data=updated_category_model)

    def delete(self, user_id: int, category_id: int) -> ServiceResponse:
        category_model = self.category_crud.get_by_id_and_user_id(user_id=user_id, id=category_id)

        if not category_model:
            return ServiceResponse(is_error=True, description=f'Category {category_id} not found')

        result = self.category_crud.delete_db_object(db_object=category_model)

        return ServiceResponse(data=result)
