from typing import Any

from fastapi import APIRouter, Depends
from app.api import schemas
from sqlalchemy.orm import Session

from app.api import deps
from app.services.test import TestService

router = APIRouter()


@router.get('/test', response_model=schemas.TestResponse)
def get_test_response(
    *,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    data = {}
    data['some_answer'] = 'EEEEEEEE'

    user_service = TestService(db=db)
    user_name = user_service.get_name_of_test_user()

    data['user_name'] = user_name

    return {'data': data}
