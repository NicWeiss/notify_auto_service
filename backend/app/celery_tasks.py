from app.core.celery_app import celery_app
from app.core.config import settings
from app.tasks.check_stuck_operation import check_stuck_operation
from app.tasks.db_clear import db_clear
from app.tasks.periondic_notify_send import periondic_notify_send
from app.tasks.time_watch import time_watch
from app.utils.logger import logger


@celery_app.on_after_configure.connect
def setup_periodic_tasks(sender, **kwargs):
    logger.info('INIT tasks')

    if settings.DO_NOT_RUN_PERIODIC_TASK:
        return

    sender.add_periodic_task(
        5,
        run_time_watcher,
        name="Start watch on the time"
    )

    sender.add_periodic_task(
        10,
        run_periodic_notify_sender,
        name="Start sending notifies"
    )

    sender.add_periodic_task(
        300,
        run_db_clear,
        name="Start DB clear"
    )

    sender.add_periodic_task(
        60,
        run_check_stuck_operation,
        name="Start check stuck operation"
    )


@celery_app.task(acks_late=True)
def run_time_watcher(*args, **kw):
    logger.info('START run_time_watcher')
    try:
        return time_watch()
    except Exception as exc:
        logger.error(f'Task error {exc}')


@celery_app.task(acks_late=True)
def run_periodic_notify_sender(*args, **kw):
    logger.info('START run_periodic_notify_sender')
    try:
        return periondic_notify_send()
    except Exception as exc:
        logger.error(f'Task error {exc}')


@celery_app.task(acks_late=True)
def run_db_clear(*args, **kw):
    logger.info('Start DB clear')
    try:
        return db_clear()
    except Exception as exc:
        logger.error(f'Task error {exc}')


@celery_app.task(acks_late=True)
def run_check_stuck_operation(*args, **kw):
    logger.info('Start check stuck operation')
    try:
        return check_stuck_operation()
    except Exception as exc:
        logger.error(f'Task error {exc}')


logger.info('INIT done')
