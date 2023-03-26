from sqlalchemy import Column, Unicode
from sqlalchemy_utils import ArrowType

from app.repo.models.base import Model


class RegstoreCode(Model):
    code = Column(Unicode)
    email = Column(Unicode)
    expire_at = Column(ArrowType, nullable=False)
