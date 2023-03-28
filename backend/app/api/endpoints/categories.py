from typing import Any

from fastapi import APIRouter, Depends
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api.response import EmberResponse
from app.services.category import CategoryService

router = APIRouter()


@router.get('/categories', response_model=schemas.EmberResponseScheme)
def get_categories(
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:

    category_service = CategoryService(db=db)
    result = category_service.get_by_user_id(user_id=user.id)
    categories = result.data

    return EmberResponse(model_name='category', data=categories)
