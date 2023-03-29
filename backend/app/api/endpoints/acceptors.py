from typing import Any

from fastapi import APIRouter, Depends
from app.api import schemas
from sqlalchemy.orm import Session, DeclarativeBase

from app.api import deps
from app.api.response import EmberResponse
from app.services.acceptor_service import AcceptorService

router = APIRouter()


@router.get('/acceptors', response_model=schemas.EmberResponseScheme)
def get_acceptors(
    db: Session = Depends(deps.get_pg_db),
    user: DeclarativeBase = Depends(deps.auth)
) -> Any:

    acceptor_service = AcceptorService(db=db)
    result = acceptor_service.get_by_user_id(user_id=user.id)
    acceptors = result.data

    return EmberResponse(model_name='acceptor', data=acceptors)
