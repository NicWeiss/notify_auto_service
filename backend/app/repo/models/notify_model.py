from sqlalchemy import Boolean, Column, ForeignKey, Unicode, Integer
from sqlalchemy.orm import relationship
from sqlalchemy_utils import ArrowType

from app.repo.models.base_model import Model


class Notify(Model):
    name = Column(Unicode)
    user_id = Column(ForeignKey('user.id'))
    text = Column(Unicode)
    periodic = Column(Unicode)
    day_of_week = Column(Integer)
    date = Column(Unicode)
    time = Column(Unicode)
    repeate_interval = Column(Integer)
    is_disabled = Column(Boolean, default=False)
    category_id = Column(ForeignKey('category.id'))
    is_autodisable = Column(Boolean, default=False)
    autodisable_at = Column(ArrowType, nullable=True)

    acceptors = relationship(
        "Acceptor",
        secondary='notify_acceptor',
        primaryjoin="Notify.id==NotifyAcceptor.notify_id",
        secondaryjoin="and_(NotifyAcceptor.acceptor_id==Acceptor.id, "
        "Acceptor.is_deleted.is_(False), Acceptor.is_disabled.is_(False))",
        lazy='joined', backref='acceptor'
    )
