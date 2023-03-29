from sqlalchemy import Column, Unicode

from app.repo.models.base_model import Model


class DateOperation(Model):
    target_date = Column(Unicode)
    complete_date = Column(Unicode)
    type = Column(Unicode)
    worker_id = Column(Unicode)
