from celery import Celery
from kombu import Queue

from app.core.config import settings


celery_app = Celery(
    'worker',
    broker=settings.RABBITMQ_DSN,
    backend=settings.REDIS_DSN
)

celery_app.conf.task_queues = [
    Queue('watcher'),
    Queue('sender'),
    Queue('clear')
]

celery_app.conf.task_routes = {
    'app.celery_tasks.run_time_watcher': {'queue': 'watcher'},
    'app.celery_tasks.run_periodic_notify_sender': {'queue': 'sender'},
    'app.celery_tasks.run_db_clear': {'queue': 'clear'},
    'app.celery_tasks.run_check_stuck_operation': {'queue': 'clear'}
}
