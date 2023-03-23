from typing import Optional
from pydantic import BaseModel
from enum import Enum


class StatusTypes(str, Enum):
    ok: 'ok'
    error: 'error'
    pending: 'pending'


class BaseError(BaseModel):
    code: str
    description: Optional[str]


class BaseData(BaseModel):
    description: Optional[str]


class BaseResult(BaseModel):
    status: StatusTypes = 'ok'
    data: Optional[BaseData]
    error: Optional[BaseError]


class ApiValidationError(BaseResult):
    status: str = 'error'
    error: BaseError
