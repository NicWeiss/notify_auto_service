from typing import Optional
from pydantic import BaseModel


class CategoryCreateScheme(BaseModel):
    user_id: int
    is_hidden: bool
    name: str


class CategoryUpdateScheme(BaseModel):
    user_id: Optional[int]
    is_hidden: Optional[bool]
    name: Optional[str]
