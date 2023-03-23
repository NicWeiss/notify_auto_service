from typing import Optional

from pydantic import BaseModel

from app.api.schemas.utils import BaseResult


class ResponseGetData(BaseModel):
    some_answer: Optional[str] = ""
    user_name: Optional[str] = ""


class TestResponse(BaseResult):
    data: ResponseGetData
