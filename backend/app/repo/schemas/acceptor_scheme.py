from typing import Optional

from pydantic import BaseModel


class AcceptorCreateScheme(BaseModel):
    user_id: int
    account: str
    is_system: bool
    name: str
    is_disabled: bool
    system_id: int


class AcceptorUpdateScheme(BaseModel):
    account: Optional[str]
    name: Optional[str]
    is_disabled: Optional[bool]
    system_id: Optional[int]
