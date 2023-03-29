from sqlalchemy import Boolean, Column, Unicode

from app.repo.models.base_model import Model


class System(Model):
    name = Column(Unicode)
    is_disabled = Column(Boolean, default=False)
    is_system = Column(Boolean, default=False)
    help = Column(Unicode)
    type = Column(Unicode)
