# ---- Prod build ----
FROM nginx:1.15-alpine
RUN rm /etc/nginx/conf.d/default.conf
COPY ./docker/ingress/nginx-dev.conf /etc/nginx/conf.d/dev.conf
COPY ./docker/ingress/nginx-prod.conf /etc/nginx/conf.d/prod.conf

