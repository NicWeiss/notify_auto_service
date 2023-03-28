from typing import Optional

from pydantic import BaseModel


class Client(BaseModel):
    client: Optional[str]
    cookies: Optional[bool]
    mobile: Optional[bool]
    os: Optional[str]
    os_version: Optional[str]
    screen: Optional[str]
    version: Optional[str]


class AuthCodeRequest(BaseModel):
    email: str


class AuthSignUp(BaseModel):
    client: Optional[Client]
    code: str
    email: str
    name: str
    password: str


class AuthLogIn(BaseModel):
    client: Optional[Client]
    email: str
    password: str
