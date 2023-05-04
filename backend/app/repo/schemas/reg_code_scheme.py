from arrow import Arrow
from pydantic import BaseModel


class RegCodeCreateScheme(BaseModel):
    email: str
    code: str
    expire_at: Arrow

    class Config:
        arbitrary_types_allowed = True
