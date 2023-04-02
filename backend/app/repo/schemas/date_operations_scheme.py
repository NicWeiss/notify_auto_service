from datetime import datetime
from typing import Dict

from pydantic import BaseModel


class DateOperationCreateScheme(BaseModel):
    date_data: Dict
    create_at: datetime
    status: str

    class Config:
        arbitrary_types_allowed = True
