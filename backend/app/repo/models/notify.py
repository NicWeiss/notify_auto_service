from sqlalchemy import Boolean, Column, ForeignKey, Unicode

from app.repo.models.base import Model


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
