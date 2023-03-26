from arrow import Arrow
from pydantic import BaseModel


class RegCodeScheme(BaseModel):
    email: str
    code: int
    expire_at: Arrow

    class Config:
        arbitrary_types_allowed = True
