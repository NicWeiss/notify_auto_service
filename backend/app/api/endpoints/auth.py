from typing import Any

from fastapi import APIRouter, Depends
from app.api import schemas
from sqlalchemy.orm import Session

from app.api import deps
from app.services.auth import AuthService

router = APIRouter()


@router.post('/auth/login', response_model=schemas.TestResponse)
def login(
    *,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    data = {}

    return {'data': data}


@router.post('/auth/sign_up', response_model=schemas.TestResponse)
def sign_up(
    *,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    data = {}

    return {'data': data}


@router.post('/auth/get_code')
def get_code(
    object_in: schemas.AuthCodeRequest,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    service = AuthService(db=db)
    service.send_verify_code_to_email(object_in.email)

    return {'data': {'email': object_in.email}}


@router.post('/auth/restore', response_model=schemas.TestResponse)
def restore(
    *,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    data = {}

    return {'data': data}


@router.post('/auth/restore/verify-restore-code', response_model=schemas.TestResponse)
def verify_restore_code(
    *,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    data = {}

    return {'data': data}


@router.post('/auth/restore/restore-password', response_model=schemas.TestResponse)
def restore_password(
    *,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    data = {}

    return {'data': data}


@router.post('/auth/restore/change-password', response_model=schemas.TestResponse)
def change_password(
    *,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    data = {}

    return {'data': data}
