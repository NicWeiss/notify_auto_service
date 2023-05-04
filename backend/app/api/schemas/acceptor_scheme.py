from typing import Optional

from pydantic import BaseModel


class CreateFields(BaseModel):
    account: str
    is_system: bool
    name: str
    is_disabled: bool
    system_id: int


class AcceptorCreateScheme(BaseModel):
    acceptor: CreateFields


class UpdateFields(BaseModel):
    account: Optional[str]
    is_system: Optional[bool]
    name: Optional[str]
    is_disabled: Optional[bool]
    system_id: Optional[int]


class AcceptorUpdateScheme(BaseModel):
    acceptor: UpdateFields


class AcceptorUpdateFcmToken(BaseModel):
    token: str
