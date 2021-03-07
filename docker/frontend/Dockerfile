# ---- Dev build ----
FROM node:10 as base

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    nginx

RUN npm install -g ember-cli

# COPY ./nginx.cfg /etc/nginx/sites-available/default

# RUN mkdir frontend

WORKDIR /frontend

COPY ./package.json ./package.json

RUN yarn install


from base as development
# RUN ember build --environment="development"


# ---- Prod build ----
FROM base as preproduction
RUN ember build --environment="production"


FROM nginx:1.15-alpine AS production

COPY --from=preproduction /frontend/dist/ /frontend/dist/
COPY ./nginx.cfg /etc/nginx/conf.d/default.conf
