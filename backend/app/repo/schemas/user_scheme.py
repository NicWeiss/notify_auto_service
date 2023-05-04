from typing import Optional
from arrow import Arrow
from pydantic import BaseModel


class UserCreateScheme(BaseModel):
    name: str
    email: str
    password: str
    registred_at: Arrow

    class Config:
        arbitrary_types_allowed = True


class UserUpdateScheme(BaseModel):
    name: Optional[str]
    email: Optional[str]
    password: Optional[str]
