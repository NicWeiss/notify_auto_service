# ---- Prod build ----
FROM node:14.16.0 as base

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    nginx

COPY ./app/ /frontend/app/
COPY ./config/ /frontend/config/
COPY ./public/ /frontend/public/
COPY ./vendor/ /frontend/vendor/

COPY ./.ember-cli /frontend/
COPY ./app.json /frontend/
COPY ./ember-cli-build.js /frontend/
COPY ./package.json /frontend/

WORKDIR /frontend

RUN npm install

RUN npm install -g ember-cli@3.24.0

RUN ember build


FROM base as preproduction
FROM nginx:1.15-alpine as production

RUN mkdir -p /dist

COPY --from=preproduction /frontend/dist/* /dist/

RUN cd /dist && ls -la
