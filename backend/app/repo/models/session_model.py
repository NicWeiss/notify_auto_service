from sqlalchemy import Column, ForeignKey, Unicode
from sqlalchemy.dialects.postgresql import JSONB
from sqlalchemy_utils import ArrowType

from app.repo.models.base_model import Model


class Session(Model):
    session = Column(Unicode)
    user_id = Column(ForeignKey('user.id'))
    client = Column(JSONB)
    location = Column(JSONB)
    expire_at = Column(ArrowType, nullable=False)
