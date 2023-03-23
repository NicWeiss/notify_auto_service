from sqlalchemy import Column, Integer, Unicode

from app.repos.models.base import Model


class User(Model):
    id = Column(Integer, primary_key=True)
    name = Column(Unicode)
    email = Column(Unicode)
    password = Column(Unicode)
