from celery import Celery
from kombu import Queue

from app.core.config import settings


celery_app = Celery(
    "worker",
    broker=settings.RABBITMQ_DSN,
    backend=settings.REDIS_DSN
)

celery_app.conf.task_queues = [
    Queue("watcher"),
    Queue("sender"),
]

celery_app.conf.task_routes = {
    "app.celery_tasks.run_time_watcher": "watcher",
    "app.celery_tasks.run_periodic_notify_sender": "sender"
}
