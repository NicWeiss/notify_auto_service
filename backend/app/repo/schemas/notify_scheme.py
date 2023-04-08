from typing import Optional, Union

from pydantic import BaseModel


class NotifyCreateScheme(BaseModel):
    user_id: int
    name: str
    text: Optional[str]
    periodic: str
    day_of_week: Optional[int]
    date: Optional[str]
    time: str
    is_disabled: bool = False
    category_id: Union[int, None]


class NotifyUpdateScheme(BaseModel):
    name: Optional[str]
    text: Optional[str]
    periodic: Optional[str]
    day_of_week: Optional[int]
    date: Optional[str]
    time: Optional[str]
    is_disabled: Optional[bool]
    category_id: Optional[Union[int, None]]
