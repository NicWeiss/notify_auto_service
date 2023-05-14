import arrow
from sqlalchemy import Column, Unicode
from sqlalchemy.dialects.postgresql import JSONB
from sqlalchemy_utils import ArrowType

from app.repo.models.base_model import Model


class DateOperation(Model):
    date_data = Column(JSONB)
    create_at = Column(ArrowType, default=arrow.utcnow)
    complete_at = Column(ArrowType)
    start_process_at = Column(ArrowType)
    status = Column(Unicode)
    worker_id = Column(Unicode)
