from typing import Any

from fastapi import APIRouter, Depends, Request
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps

router = APIRouter()


@router.get('/notifies')
def get_notifies(
    request: Request,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:

    return {'notify': []}
