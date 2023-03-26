from sqlalchemy import Column, Unicode

from app.repo.models.base import Model


class User(Model):
    name = Column(Unicode)
    email = Column(Unicode)
    password = Column(Unicode)
