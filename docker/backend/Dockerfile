FROM python:3.10-slim as base

ENV PYTHONDONTWRITEBYTECODE yes

RUN apt-get update -y && \
    apt-get install -y  libpq-dev python3-dev gcc lynx && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mkdir backend
COPY ./backend /backend
WORKDIR /backend
RUN pip install -r requirements.txt
RUN mkdir -p /var/run/celery/

# ---- PROD build ----
FROM base as prod
CMD ["python", "run.py"]

# ---- PROD build ----
FROM base as celery
CMD ["bash", "./start_celery_worker.sh"]
