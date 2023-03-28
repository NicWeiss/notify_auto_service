from typing import Optional

from arrow import Arrow
from pydantic import BaseModel


class Location(BaseModel):
    ip: Optional[str]
    city: Optional[str]
    region: Optional[str]
    country_name: Optional[str]
    continent_name: Optional[str]


class Client(BaseModel):
    screen: Optional[str]
    client: Optional[str]
    version: Optional[str]
    mobile: Optional[bool]
    os: Optional[str]
    os_version: Optional[str]
    cookies: Optional[bool]


class SessionCreateScheme(BaseModel):

    session: str
    user_id: int
    client: Optional[Client]
    location: Optional[Location]
    expire_at: Arrow

    class Config:
        arbitrary_types_allowed = True
