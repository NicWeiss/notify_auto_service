from datetime import datetime
from typing import Dict, Optional

from arrow import Arrow
from pydantic import BaseModel


class DateOperationCreateScheme(BaseModel):
    date_data: Dict
    create_at: datetime
    status: str

    class Config:
        arbitrary_types_allowed = True


class DateOperationUpdateScheme(BaseModel):
    status: Optional[str]
    complete_at: Optional[Arrow]

    class Config:
        arbitrary_types_allowed = True
