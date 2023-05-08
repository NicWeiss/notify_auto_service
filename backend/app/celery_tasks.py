import logging

from app.core.celery_app import celery_app
from app.core.config import settings

from app.tasks.time_watch import time_watch
from app.tasks.periondic_notify_send import periondic_notify_send

logger = logging.getLogger(__name__)


@celery_app.on_after_configure.connect
def setup_periodic_tasks(sender, **kwargs):
    print('INIT tasks')

    if settings.DO_NOT_RUN_PERIODIC_TASK:
        return

    sender.add_periodic_task(
        5,
        run_time_watcher,
        name="Start watch on the time"
    )

    sender.add_periodic_task(
        30,
        run_periodic_notify_sender,
        name="Start sending notifies"
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


logger.info('INIT done')
