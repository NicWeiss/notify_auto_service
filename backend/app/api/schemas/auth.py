from pydantic import BaseModel


class AuthCodeRequest(BaseModel):
    email: str
