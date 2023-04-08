import threading
from time import sleep

from app.repo import LocalSession
from app.services.date_operation_service import DateOperationService
from app.services.notify_send_service import NotifySenderService

IN_RUN = {}


def process_operation(operation, callback):
    db = LocalSession()
    try:
        process_service = NotifySenderService(db=db)
        operation_service = DateOperationService(db=db)

        result = operation_service.get_operation_by_id(id=operation.id)

        process_service.process_operation(operation=result.data)
    except Exception as exc:
        print(exc)

    db.close()
    callback(operation.id)


def done_operation(id):
    IN_RUN.pop(id)


def periondic_notify_send():
    db = LocalSession()
    operation_service = DateOperationService(db=db)
    result = operation_service.get_operation_ids_for_process()
    operations = result.data

    if not operations:
        return

    for operation in operations:
        while len(IN_RUN.keys()) > 8:
            sleep(0.001)

        IN_RUN[operation.id] = True
        op_tr = threading.Thread(name='N_Thread', target=process_operation, args=[operation, done_operation])
        op_tr.start()

    # result_lists = await asyncio.gather(*tasks)
