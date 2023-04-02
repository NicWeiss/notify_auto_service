from typing import Any, Optional

from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api import schemas
from app.api.response import EmberResponse
from app.services.notify_service import NotifyService

router = APIRouter()


@router.get('/notifies', response_model=schemas.EmberResponseScheme)
def get_notifies(
    category_id: Optional[int] = None,
    page: Optional[int] = None,
    per_page: Optional[int] = None,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    notify_service = NotifyService(db=db)
    result = notify_service.get_list(user_id=user.id, category_id=category_id, page=page, per_page=per_page)
    notifies = result.data
    meta = result.meta

    return EmberResponse(model_name='notify', data=notifies, total_pages=meta.get('total_pages', 1))


@router.put('/notifies/reset_from_category_id')
def reset_by_category_id(
    object_in: schemas.NotifyResetCategoryScheme,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    notify_service = NotifyService(db=db)
    notify_service.reset_category(user_id=user.id, category_id=object_in.category_id)

    return {'status': 'ok'}


@router.delete('/notifies/delete_by_category_id')
def delete_by_category_id(
    object_in: schemas.NotifyDeleteByCategoryScheme,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    notify_service = NotifyService(db=db)
    notify_service.delete_by_category(user_id=user.id, category_id=object_in.category_id)
    return {'status': 'ok'}


@router.post('/notifies', response_model=schemas.EmberResponseScheme)
def create_notify(
    object_in: schemas.NotifyCreateScheme,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    notify_data_in = object_in.notify

    notify_service = NotifyService(db=db)
    result = notify_service.create(user_id=user.id, **notify_data_in.dict())

    notify = result.data

    return EmberResponse(model_name='notify', data=notify)


@router.put('/notifies/{notify_id}', response_model=schemas.EmberResponseScheme)
def update_notify(
    notify_id: int,
    object_in: schemas.NotifyUpdateScheme,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    notify_data_in = object_in.notify

    notify_service = NotifyService(db=db)
    result = notify_service.update(user_id=user.id, notify_id=notify_id, **notify_data_in.dict())

    if result.is_error:
        raise HTTPException(status_code=404, detail=result.description)

    notify = result.data

    return EmberResponse(model_name='notify', data=notify)


@router.delete('/notifies/{notify_id}')
def delete_notify(
    notify_id: int,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    notify_service = NotifyService(db=db)
    result = notify_service.delete(
        user_id=user.id,
        notify_id=notify_id
    )

    if result.is_error:
        raise HTTPException(status_code=404, detail=result.description)

    return EmberResponse(model_name='notify', data=True)
