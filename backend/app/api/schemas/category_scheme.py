from pydantic import BaseModel


class CreateFields(BaseModel):
    is_hidden: bool
    name: str


class CategoryCreateScheme(BaseModel):
    category: CreateFields


class CategoryUpdateScheme(BaseModel):
    category: CreateFields
