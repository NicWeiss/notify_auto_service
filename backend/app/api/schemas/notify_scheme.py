from typing import List, Optional, Union

import arrow
import dateutil.parser
from pydantic import BaseModel, validator


class NotifyResetCategoryScheme(BaseModel):
    category_id: int


class NotifyDeleteByCategoryScheme(BaseModel):
    category_id: int


class Acceptor(BaseModel):
    id: int


class CreateFields(BaseModel):
    acceptors: List[Acceptor]
    date: Optional[str]
    day_of_week: Optional[int]
    is_disabled: bool
    name: str
    periodic: str
    text: Optional[str]
    time: str
    category_id: Union[int, None]
    repeate_interval: Optional[int]
    is_autodisable: Optional[bool]
    autodisable_at: Optional[str]

    @validator("autodisable_at")
    @classmethod  # Optional, but your linter may like it.
    def arrow_type(cls, value):
        result = arrow.get(dateutil.parser.isoparse(value))
        return result


class NotifyCreateScheme(BaseModel):
    notify: CreateFields


class UpdateFields(BaseModel):
    acceptors: List[Optional[Acceptor]]
    date: Optional[str]
    day_of_week: Optional[int]
    is_disabled: Optional[bool]
    name: Optional[str]
    periodic: Optional[str]
    text: Optional[str]
    time: Optional[str]
    category_id: Optional[Union[int, None]]
    repeate_interval: Optional[int]
    is_autodisable: Optional[bool]
    autodisable_at: Optional[str]

    @validator("autodisable_at")
    @classmethod  # Optional, but your linter may like it.
    def arrow_type(cls, value):
        result = arrow.get(dateutil.parser.isoparse(value))
        return result


class NotifyUpdateScheme(BaseModel):
    notify: UpdateFields
