from typing import Any

from fastapi import APIRouter, Depends, HTTPException
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api.response import EmberResponse
from app.services.category_service import CategoryService

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


@router.post('/categories', response_model=schemas.EmberResponseScheme)
def create_categories(
    object_in: schemas.CategoryCreateScheme,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    category_data_in = object_in.category

    category_service = CategoryService(db=db)
    result = category_service.create(
        user_id=user.id,
        name=category_data_in.name,
        is_hidden=category_data_in.is_hidden)
    category = result.data

    return EmberResponse(model_name='category', data=category)


@router.put('/categories/{category_id}', response_model=schemas.EmberResponseScheme)
def update_category(
    category_id: int,
    object_in: schemas.CategoryUpdateScheme,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    category_data_in = object_in.category

    category_service = CategoryService(db=db)
    result = category_service.update(
        user_id=user.id,
        category_id=category_id,
        name=category_data_in.name,
        is_hidden=category_data_in.is_hidden)

    if result.is_error:
        raise HTTPException(status_code=404, detail=result.description)

    category = result.data

    return EmberResponse(model_name='category', data=category)


@router.delete('/categories/{category_id}')
def delete_category(
    category_id: int,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    category_service = CategoryService(db=db)
    result = category_service.delete(
        user_id=user.id,
        category_id=category_id
    )

    if result.is_error:
        raise HTTPException(status_code=404, detail=result.description)

    return EmberResponse(model_name='category', data=True)
