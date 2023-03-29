from typing import Any, Optional

from fastapi import APIRouter, Depends
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api.response import EmberResponse
from app.services.notify_service import NotifyService

router = APIRouter()


@router.get('/notifies', response_model=schemas.EmberResponseScheme)
def get_categories(
    category_id: int,
    page: Optional[int],
    per_page: Optional[int],
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    notify_service = NotifyService(db=db)
    result = notify_service.get_list(user_id=user.id, category_id=category_id, page=page, per_page=per_page)
    notifies = result.data

    return EmberResponse(model_name='notify', data=notifies)
