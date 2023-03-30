from sqlalchemy import Boolean, Column, ForeignKey, Unicode

from app.repo.models.base_model import Model


class Category(Model):
    name = Column(Unicode)
    user_id = Column(ForeignKey('user.id'))
    is_hidden = Column(Boolean)
