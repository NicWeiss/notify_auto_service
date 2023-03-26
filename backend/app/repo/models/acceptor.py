from sqlalchemy import Column, ForeignKey, Unicode, Boolean

from app.repo.models.base import Model


class Acceptor(Model):
    name = Column(Unicode)
    system_id = Column(ForeignKey('system.id'))
    user_id = Column(ForeignKey('user.id'))
    account = Column(Unicode)
    is_disabled = Column(Boolean, default=False)
    is_system = Column(Boolean, default=False)
