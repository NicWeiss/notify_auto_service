from typing import Any, Dict, List, Union

from sqlalchemy import and_
from sqlalchemy.orm import Session
from app.repo.models.base_model import Model
from pydantic import BaseModel


class Crud:
    def __init__(self, db: Session, model: Model):
        self.db = db
        self.model = model

    def get_by_id(self, id):
        return self.db.query(self.model).filter(and_(self.model.is_deleted.is_(False), self.model.id == id)).first()

    def get_all_by_user_id(self, user_id: str) -> List[Model]:
        return self.db.query(self.model).filter(
            and_(
                self.model.is_deleted.is_(False),
                self.model.user_id == user_id
            )
        ).all()

    def get_by_id_and_user_id(self, id: int, user_id: str) -> List[Model]:
        return self.db.query(self.model).filter(and_(
            self.model.is_deleted.is_(False),
            self.model.id == id,
            self.model.user_id == user_id
        )).first()

    def create(self, scheme: BaseModel) -> Model:
        db_object = self.model(**scheme.dict(exclude_unset=True))
        self.db.add(db_object)
        self.db.commit()
        self.db.refresh(db_object)

        return db_object

    def update(
        self,
        db_object: Model,
        scheme: Union[BaseModel, Dict[str, Any]]
    ) -> Model:
        if isinstance(scheme, dict):
            update_data = scheme
        else:
            update_data = scheme.dict(exclude_unset=True)

        for field in db_object.__dict__:
            if field in update_data:
                setattr(db_object, field, update_data[field])

        self.db.add(db_object)
        self.db.commit()
        self.db.refresh(db_object)

        return db_object

    def remove_by_id(self, id: int) -> bool:
        db_object = self.db.query(self.model).get(id)
        self.db.delete(db_object)
        self.db.commit()

        return True

    def remove_db_object(self, db_object: Model) -> bool:
        self.db.delete(db_object)
        self.db.commit()

        return True

    def delete_by_id(self, id: int) -> bool:
        self.db.query(self.model).filter(self.model.id == id).update({"is_deleted": True})
        self.db.commit()

        return True

    def delete_db_object(self, db_object: Model) -> bool:
        db_object.is_deleted = True

        self.db.add(db_object)
        self.db.commit()
        self.db.refresh(db_object)

        return True
