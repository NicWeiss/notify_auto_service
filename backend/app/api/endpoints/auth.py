from typing import Any

from fastapi import APIRouter, Depends, HTTPException, Request
from app.api import schemas
from sqlalchemy.orm import Session

from app.api import deps
from app.services.auth_service import AuthService
from app.services.session_service import SessionService
from app.services.user_service import UserService

router = APIRouter()


@router.post('/auth/login')
def login(
    object_in: schemas.AuthLogIn,
    request: Request,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    user_service = UserService(db=db)
    result = user_service.get_by_password_and_email(password=object_in.password, email=object_in.email)

    if result.is_error:
        raise HTTPException(status_code=422, detail=result.description)

    user = result.data

    session_service = SessionService(db=db)
    result = session_service.set_session(user_id=user.id, user_ip=request.client.host, session_data=object_in)

    return {'session': result.data}


@router.post('/auth/sign_up')
def sign_up(
    object_in: schemas.AuthSignUp,
    request: Request,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    auth_service = AuthService(db=db)
    result = auth_service.verify_code_and_email(code=object_in.code, email=object_in.email)

    if result.is_error:
        raise HTTPException(status_code=422, detail=result.description)

    user_service = UserService(db=db)
    result = user_service.register_new_user(user_data=object_in)
    user = result.data

    session_service = SessionService(db=db)
    result = session_service.set_session(user_id=user.id, user_ip=request.client.host, session_data=object_in)
    auth_service.clear_reg_code(code=object_in.code, email=object_in.email)

    return {'session': result.data}


@router.post('/auth/get_code')
def get_code(
    object_in: schemas.AuthCodeRequest,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    service = AuthService(db=db)
    result = service.send_verify_code_to_email(object_in.email)

    if result.is_error:
        raise HTTPException(status_code=422, detail=result.description)

    return {'status': result.status}


@router.post('/auth/restore')
def restore(
    object_in: schemas.AuthRestore,
    request: Request,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    service = AuthService(db=db)
    result = service.send_restore_code_to_email(
        protocol=request.scope['scheme'],
        domain=request.scope['headers'][0][1].decode(),
        email=object_in.email
    )

    if result.is_error:
        raise HTTPException(status_code=422, detail=result.description)

    return {'data': True}


@router.post('/auth/restore/verify-restore-code')
def verify_restore_code(
    object_in: schemas.AuthRestoreVerifyCode,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    service = AuthService(db=db)
    result = service.verify_restore_code(code=object_in.code)

    if result.is_error:
        raise HTTPException(status_code=422, detail=result.description)

    return {'data': {}}


@router.post('/auth/restore/restore-password')
def restore_password(
    object_in: schemas.AuthRestorePassword,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    auth_service = AuthService(db=db)
    result = auth_service.verify_restore_code(code=object_in.code)

    if result.is_error:
        raise HTTPException(status_code=422, detail=result.description)

    reg_code = result.data
    user_service = UserService(db=db)

    result = user_service.change_password_by_code(email=reg_code.email, password=object_in.password)

    if result.is_error:
        raise HTTPException(status_code=422, detail=result.description)

    result = auth_service.clear_reg_code(code=object_in.code, email=reg_code.email)

    return {'data': {}}
