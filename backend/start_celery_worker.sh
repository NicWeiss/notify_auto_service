#!/bin/bash
celery -A app.celery_tasks beat &
celery -A app.celery_tasks worker --loglevel=info --concurrency=1 -E -n notifier-worker@4.0.0.%h -Q watcher,sender -s /var/run/celery/beat-schedule
