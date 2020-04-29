FROM php:7.1.33-cli

ENV APP_DIR=/srv/app \
    BUILD_SCRIPTS_DIR=/build-scripts \
    PANTHER_NO_SANDBOX=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       zip=3.0-* \
       unzip=6.0-* \
       git=1:2.20.* \
       libzip-dev=1.5.* \
       wget=1.20.1-1.1 \
       gpg=2.2.* \
       zlib1g-dev=1:1.2.* \
       chromium=80.0.* \
    && pecl install xdebug \
    && docker-php-ext-install opcache zip \
    && docker-php-ext-enable xdebug opcache zip \
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

# Blackfire {@see https://blackfire.io/docs/integrations/docker/php-docker}
RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz \
    && mkdir -p /tmp/blackfire \
    && curl -A "Docker" -L https://blackfire.io/api/v1/releases/client/linux_static/amd64 | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    && rm -Rf /tmp/blackfire

WORKDIR $APP_DIR

CMD ["/bin/bash", "-c"]
