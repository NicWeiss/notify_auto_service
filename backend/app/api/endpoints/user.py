from typing import Any

from fastapi import APIRouter, Depends
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api.response import EmberResponse

router = APIRouter()


@router.get('/user', response_model=schemas.EmberResponseScheme)
def get_user(
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    return EmberResponse(model_name='user', data=user)
