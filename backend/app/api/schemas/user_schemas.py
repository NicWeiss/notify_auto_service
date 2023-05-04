from typing import Optional

from pydantic import BaseModel


class UserChangePassword(BaseModel):
    current_password: str
    new_password: str


class UpdateFields(BaseModel):
    email: Optional[str]
    name: Optional[str]


class UserUpdateScheme(BaseModel):
    user: UpdateFields


class UserResponseScheme(BaseModel):
    id: int
    email: str
    name: str
    registred_at: str
    is_deleted: bool
