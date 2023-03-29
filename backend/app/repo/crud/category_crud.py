from app.repo.models.category_model import Category
from app.repo.crud.base_crud import Crud


class CategoryCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=Category)
