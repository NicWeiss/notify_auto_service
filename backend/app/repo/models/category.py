from sqlalchemy import Boolean, Column, ForeignKey, Unicode

from app.repo.models.base import Model


class Category(Model):
    name = Column(Unicode)
    user_id = Column(ForeignKey('user.id'))
    is_hiden = Column(Boolean)
