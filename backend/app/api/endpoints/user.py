from typing import Any

from fastapi import APIRouter, Depends, HTTPException
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api.response import EmberResponse
from app.services.user_service import UserService

router = APIRouter()


@router.get('/user', response_model=schemas.EmberResponseScheme)
def get_user(
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    return EmberResponse(model_name='user', scheme=schemas.UserResponseScheme, data=user)


@router.put('/users/{user_id}', response_model=schemas.EmberResponseScheme)
def update_notify(
    user_id: int,
    object_in: schemas.UserUpdateScheme,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    user_data_in = object_in.user

    user_service = UserService(db=db)
    result = user_service.update(user=user, **user_data_in.dict())

    if result.is_error:
        raise HTTPException(status_code=404, detail=result.description)

    user = result.data

    return EmberResponse(model_name='user', scheme=schemas.UserResponseScheme, data=user)


@router.post('/user/change-password')
def change_password(
    object_in: schemas.UserChangePassword,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    user_service = UserService(db=db)

    result = user_service.change_password(
        user=user,
        current_password=object_in.current_password,
        new_password=object_in.new_password
    )

    if result.is_error:
        raise HTTPException(status_code=422, detail=result.description)

    return {'data': {}}
