# ---- Prod build ----
FROM node:16.16.0 as base

RUN sed -i 's/stable\/updates/stable-security\/updates/' /etc/apt/sources.list
RUN apt-get update && \
  apt-get install -y --no-install-recommends \
  nginx
COPY ./frontend/app/ /frontend/app/
COPY ./frontend/config/ /frontend/config/
COPY ./frontend/public/ /frontend/public/
COPY ./frontend/vendor/ /frontend/vendor/

COPY ./frontend/.ember-cli /frontend/
COPY ./frontend/app.json /frontend/
COPY ./frontend/ember-cli-build.js /frontend/
COPY ./frontend/package.json /frontend/

WORKDIR /frontend

RUN npm install
RUN npm install -g ember-cli@3.24.0


# ---- Dev build ----
FROM base as dev
RUN ls -la

# ---- Prod build ----
FROM base as preproduction

RUN ember build --environment=production
RUN mv /frontend/dist /dist


# ---- Prod packing ----
FROM nginx:1.15-alpine AS prod

RUN mkdir -p /dist
RUN rm -rf /frontend
COPY --from=preproduction /dist/* /dist/
COPY ./docker/frontend/nginx.conf /etc/nginx/conf.d/default.conf

