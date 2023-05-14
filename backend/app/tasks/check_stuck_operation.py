from app.repo import LocalSession
from app.services.date_operation_service import DateOperationService


def check_stuck_operation():
    db = LocalSession()

    try:
        operation_service = DateOperationService(db=db)
        operation_service.check_stuck_db_clearoperation()
    except Exception as exc:
        raise exc
    else:
        db.commit()
