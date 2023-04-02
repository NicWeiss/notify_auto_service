from app.repo import LocalSession
from app.services.date_operation_service import DateOperationService


def time_watch():
    db = LocalSession()

    try:
        service = DateOperationService(db=db)
        service.create_new_operations()
    except Exception as exc:
        raise exc
    else:
        db.commit()
