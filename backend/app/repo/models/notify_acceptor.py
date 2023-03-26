from sqlalchemy import Column, ForeignKey, Unicode

from app.repo.models.base import Model


class NotifyAcceptor(Model):
    name = Column(Unicode)
    notify_id = Column(ForeignKey('notify.id'))
    acceptor_id = Column(ForeignKey('acceptor.id'))
