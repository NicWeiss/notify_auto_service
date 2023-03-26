from sqlalchemy import Column, Integer, Boolean
from sqlalchemy.ext.declarative import declared_attr, as_declarative

from app.utils import helper as h


@as_declarative()
class Model():

    id = Column(Integer, primary_key=True)
    is_deleted = Column(Boolean, default=False)

    @declared_attr
    def __tablename__(cls):
        return h.uncamelcase(cls.__name__)
