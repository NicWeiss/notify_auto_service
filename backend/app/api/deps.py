from typing import Generator

from fastapi import Depends, Header, HTTPException
from sqlalchemy.orm import Session

from app.repo import LocalSession
from app.services.session_service import SessionService
from app.services.user_service import UserService


def get_pg_db() -> Generator:
    try:
        db = LocalSession()
        yield db
    finally:
        db.close()


async def auth(session: str = Header(), db: Session = Depends(get_pg_db)):
    if not session:
        raise HTTPException(status_code=403, detail="Auth required")

    session_service = SessionService(db=db)
    result = session_service.get_session(session=session)

    if result.is_error:
        raise HTTPException(status_code=403, detail="Auth required")

    session_db_object = result.data

    user_service = UserService(db=db)
    result = user_service.get_by_id(id=session_db_object.user_id)

    if result.is_error:
        raise HTTPException(status_code=403, detail="Auth required")

    return result.data
