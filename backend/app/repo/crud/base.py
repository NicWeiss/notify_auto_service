from typing import Any, Dict, Union

from sqlalchemy.orm import Session
from app.repo.models.base import Model
from pydantic import BaseModel


class Crud:
    def __init__(self, db: Session, model: Model):
        self.db = db
        self.model = model

    def get_by_id(self, id):
        return self.db.query(self.model).filter(self.model.id == id).first()

    def create(self, scheme: BaseModel) -> Model:
        model = self.model(**scheme.__dict__)
        self.db.add(model)
        self.db.commit()
        self.db.refresh(model)

        return model

    def update(
        self,
        model: Model,
        scheme: Union[BaseModel, Dict[str, Any]]
    ) -> Model:
        if isinstance(scheme, dict):
            update_data = scheme
        else:
            update_data = scheme.dict(exclude_unset=True)

        for field in model.__dict__:
            if field in update_data:
                setattr(model, field, update_data[field])

        self.db.add(model)
        self.db.commit()
        self.db.refresh(model)

        return model
