from sqlalchemy import Column, ForeignKey

from app.repo.models.base_model import Model


class NotifyAcceptor(Model):
    notify_id = Column(ForeignKey('notify.id'))
    acceptor_id = Column(ForeignKey('acceptor.id'))
