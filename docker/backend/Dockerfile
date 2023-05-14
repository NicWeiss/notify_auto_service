FROM python:3.10-slim as base

ENV PYTHONDONTWRITEBYTECODE yes

RUN apt-get update -y && \
    apt-get install -y  libpq-dev python3-dev gcc lynx && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mkdir backend
COPY ./backend /backend
WORKDIR /backend
RUN pip install --upgrade pip && pip3 install -r requirements.txt
RUN mkdir -p /var/run/celery/

# ---- PROD build ----
FROM base as prod
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8800", "--proxy-headers"]

# ---- PROD build ----
FROM base as celery
CMD ["bash", "./start_celery_worker.sh"]
