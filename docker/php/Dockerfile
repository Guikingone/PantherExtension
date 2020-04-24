FROM php:7.2.22-cli

ENV APP_DIR=/srv/app \
    BUILD_SCRIPTS_DIR=/build-scripts

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       zip=3.0-* \
       unzip=6.0-* \
       git=1:2.20.* \
       libzip-dev=1.5.* \
       wget=1.20.1-1.1 \
       gpg=2.2.* \
    && docker-php-ext-install opcache zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Add Composer
COPY composer.sh $BUILD_SCRIPTS_DIR/composer.sh
RUN chmod +x $BUILD_SCRIPTS_DIR/composer.sh
RUN $BUILD_SCRIPTS_DIR/composer.sh

RUN groupmod -g 80 dialout \
    && mkdir -p $APP_DIR /tmp

COPY launcher.sh /usr/local/bin/launcher
RUN chmod +x /usr/local/bin/launcher
ENTRYPOINT ["launcher"]

WORKDIR $APP_DIR

CMD ["/bin/bash", "-c"]
