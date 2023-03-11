FROM registry.kubernetes.infra.optimy.net/tools/web-ci:7.3 AS build 

COPY --chown=www-data:www-data . /optimy 
RUN rm /optimy/Dockerfile /optimy/Dockerfile.preprod.tpl /optimy/Dockerfile.staging.tpl

# Install nodeJS
RUN apt-get update && apt-get install curl -y
RUN curl -sL https://deb.nodesource.com/setup_13.x | bash -
RUN apt-get install -y nodejs

# Install api-admin dependencies
RUN for i in admin-api api ; do cd /optimy/$i ; composer install ; cd .. ; done 
RUN for i in admin-api api ; do cd /optimy/$i ; npm install ; cd .. ; done 

# Frontend
COPY ./frontend /tmp 
RUN cd /tmp && mv vue-prod.config.js vue.config.js && npm install && npm rebuild node-sass && npm run build
RUN cp -R /tmp/dist/* /optimy/frontend/public
RUN cp /tmp/htaccess /optimy/frontend/public/.htaccess

FROM registry.kubernetes.infra.optimy.net/tools/web-ci:7.3
COPY --from=build --chown=www-data:www-data /optimy /optimy 
# Api-doc
ENV SWAGGER_API_JSON="/optimy/api-doc/cip-api.json"

# Admin-api-doc
ENV SWAGGER_ADMIN_API_JSON="/optimy/admin-api-doc/cip-admin-api.json"