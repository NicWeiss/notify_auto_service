from typing import Any

from fastapi import APIRouter, Depends
from app.api import schemas
from sqlalchemy.orm import Session

from app.api import deps
from app.repos.crud.user import UserCrud

router = APIRouter()


@router.get('/test', response_model=schemas.TestResponse)
def get_test_response(
    *,
    db: Session = Depends(deps.get_pg_db)
) -> Any:
    data = {}
    data['some_answer'] = 'EEEEEEEE'

    user_crud = UserCrud(db=db)
    user = user_crud.get_by_id(1)

    data['user_name'] = user.name

    return {'data': data}
