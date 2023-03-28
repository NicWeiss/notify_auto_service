from sqlalchemy import Column, Unicode
from sqlalchemy_utils import ArrowType

from app.repo.models.base import Model


class User(Model):
    name = Column(Unicode)
    email = Column(Unicode)
    password = Column(Unicode)
    registred_at = Column(ArrowType)
