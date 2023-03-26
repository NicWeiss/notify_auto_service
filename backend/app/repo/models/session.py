from sqlalchemy import Column, ForeignKey, Unicode
from sqlalchemy_utils import ArrowType

from app.repo.models.base import Model


class Session(Model):
    session = Column(Unicode)
    user_id = Column(ForeignKey('user.id'))
    client = Column(Unicode)
    location = Column(Unicode)
    expire_at = Column(ArrowType, nullable=False)
