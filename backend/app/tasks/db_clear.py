from app.repo import LocalSession
from app.services.date_operation_service import DateOperationService
from app.services.session_service import SessionService


def db_clear():
    db = LocalSession()

    try:
        operation_service = DateOperationService(db=db)
        session_service = SessionService(db=db)

        operation_service.clear_done_operations()
        session_service.clear_expires_sessions()
    except Exception as exc:
        raise exc
    else:
        db.commit()
