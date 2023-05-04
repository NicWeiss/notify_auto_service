from typing import Any

from fastapi import APIRouter, Depends
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api.response import EmberResponse
from app.services.session_service import SessionService

router = APIRouter()


@router.get('/sessions', response_model=schemas.EmberResponseScheme)
def get_sessions(
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    session_service = SessionService(db=db)
    result = session_service.get_by_user_id(user_id=user.id)
    sessions = result.data

    return EmberResponse(model_name='session', data=sessions)


@router.delete('/sessions/{session_id}')
def update_category(
    session_id: int,
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:
    session_service = SessionService(db=db)
    session_service.delete_session(user_id=user.id, session_id=session_id)

    return EmberResponse(model_name='session', data=True)
