# ---- Dev build ----
FROM node:14.16.0 as base

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    nginx

RUN npm install -g ember-cli

COPY ./.ember-cli /frontend/
COPY ./app.json /frontend/
COPY ./ember-cli-build.js /frontend/
COPY ./package.json /frontend/

WORKDIR /frontend

RUN npm install
