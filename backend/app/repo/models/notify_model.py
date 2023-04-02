from sqlalchemy import Boolean, Column, ForeignKey, Unicode
from sqlalchemy.orm import relationship

from app.repo.models.base_model import Model


class Notify(Model):
    name = Column(Unicode)
    user_id = Column(ForeignKey('user.id'))
    text = Column(Unicode)
    periodic = Column(Unicode)
    day_of_week = Column(Unicode)
    date = Column(Unicode)
    time = Column(Unicode)
    is_disabled = Column(Boolean, default=False)
    category_id = Column(ForeignKey('category.id'))

    acceptors = relationship(
        "Acceptor",
        secondary='notify_acceptor',
        primaryjoin="Notify.id==NotifyAcceptor.notify_id",
        secondaryjoin="and_(NotifyAcceptor.acceptor_id==Acceptor.id, "
        "Acceptor.is_deleted.is_(False), Acceptor.is_disabled.is_(False))",
        lazy='joined', backref='acceptor')
