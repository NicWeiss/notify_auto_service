from pydantic import BaseModel


class CategoryCreateScheme(BaseModel):
    user_id: int
    is_hidden: bool
    name: str
