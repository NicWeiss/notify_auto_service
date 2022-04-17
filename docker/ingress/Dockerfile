# ---- Prod build ----
FROM nginx:1.15-alpine as base
RUN rm /etc/nginx/conf.d/default.conf
COPY ./docker/ingress/nginx-dev.conf /etc/nginx/conf.d/dev.conf


# ---- Prod packing ----
FROM nginx:1.15-alpine AS prod
RUN rm /etc/nginx/conf.d/dev.conf
COPY ./docker/ingress/nginx-prod.conf /etc/nginx/conf.d/prod.conf
