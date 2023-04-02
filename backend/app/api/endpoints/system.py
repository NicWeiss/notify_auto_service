from typing import Any

from fastapi import APIRouter, Depends
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api.response import EmberResponse
from app.services.system_service import SystemService

router = APIRouter()


@router.get('/systems', response_model=schemas.EmberResponseScheme)
def get_categories(
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    system_service = SystemService(db=db)
    result = system_service.get_list()
    systems = result.data

    return EmberResponse(model_name='system', data=systems)


@router.get('/systems/{system_id}', response_model=schemas.EmberResponseScheme)
def update_category(
    system_id: int,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    system_service = SystemService(db=db)
    result = system_service.get_by_id(system_id=system_id)
    system = result.data

    return EmberResponse(model_name='system', data=system)
