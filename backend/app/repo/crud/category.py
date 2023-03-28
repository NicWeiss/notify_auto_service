from app.repo.models.category import Category
from app.repo.crud.base import Crud


class CategoryCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=Category)
