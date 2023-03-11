FROM registry.kubernetes.infra.optimy.net/ci/ci-source:{{GO_PIPELINE_LABEL}} AS build 

# Frontend

# Install nodeJS

RUN apt-get update && apt-get install curl -y
RUN curl -sL https://deb.nodesource.com/setup_13.x | bash -
RUN apt-get install -y nodejs

COPY ./frontend /tmp
RUN cd /tmp && mv vue-prod.config.js vue.config.js && npm install && npm rebuild node-sass && npm run build-preprod

RUN cp -R /tmp/dist/* /optimy/frontend/public
RUN cp /tmp/htaccess /optimy/frontend/public/.htaccess

RUN rm -rf /tmp/*

FROM registry.kubernetes.infra.optimy.net/tools/web-ci:7.3

COPY --chown=www-data:www-data --from=build /optimy /optimy
# Api-doc and Api 
ENV SWAGGER_API_JSON="/optimy/api-doc/cip-api.json"
ENV SWAGGER_ADMIN_API_JSON="/optimy/admin-api-doc/cip-admin-api.json"



