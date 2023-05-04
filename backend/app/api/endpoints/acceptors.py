from typing import Any

from fastapi import APIRouter, Depends, HTTPException
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api.response import EmberResponse
from app.services.acceptor_service import AcceptorService

router = APIRouter()


@router.get('/acceptors', response_model=schemas.EmberResponseScheme)
def get_acceptors(
    only_enabled: bool = False,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:

    acceptor_service = AcceptorService(db=db)
    result = acceptor_service.get_by_user_id(user_id=user.id, only_enabled=only_enabled)
    acceptors = result.data

    return EmberResponse(model_name='acceptor', data=acceptors)


@router.post('/acceptors', response_model=schemas.EmberResponseScheme)
def create_acceptor(
    object_in: schemas.AcceptorCreateScheme,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    acceptor_data_in = object_in.acceptor

    acceptor_service = AcceptorService(db=db)
    result = acceptor_service.create(user_id=user.id, **acceptor_data_in.dict())

    acceptor = result.data

    return EmberResponse(model_name='acceptor', data=acceptor)


@router.put('/acceptors/{acceptor_id}', response_model=schemas.EmberResponseScheme)
def update_acceptor(
    acceptor_id: int,
    object_in: schemas.AcceptorUpdateScheme,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    acceptor_data_in = object_in.acceptor

    acceptor_service = AcceptorService(db=db)
    result = acceptor_service.update(user_id=user.id, acceptor_id=acceptor_id, **acceptor_data_in.dict())

    if result.is_error:
        raise HTTPException(status_code=404, detail=result.description)

    acceptor = result.data

    return EmberResponse(model_name='acceptor', data=acceptor)


@router.delete('/acceptors/{acceptor_id}')
def delete_acceptor(
    acceptor_id: int,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    acceptor_service = AcceptorService(db=db)
    result = acceptor_service.delete(
        user_id=user.id,
        acceptor_id=acceptor_id
    )

    if result.is_error:
        raise HTTPException(status_code=404, detail=result.description)

    return EmberResponse(model_name='acceptor', data=True)


@router.post('/acceptors/update_push_tokens')
def update_push_tokens(
    object_in: schemas.AcceptorUpdateFcmToken,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    acceptor_service = AcceptorService(db=db)
    result = acceptor_service.update_push_tokens(
        user_id=user.id,
        token=object_in.token
    )

    if result.is_error:
        raise HTTPException(status_code=404, detail=result.description)

    return EmberResponse(model_name='acceptor', data=True)
