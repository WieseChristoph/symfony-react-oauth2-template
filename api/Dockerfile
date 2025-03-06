FROM composer AS builder

WORKDIR /app

COPY composer.json .
COPY composer.lock .

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist

FROM debian AS runner

WORKDIR /var/www/app

RUN apt-get update -q && \
    apt-get install -y wget apt-transport-https lsb-release ca-certificates

# Add PHP repository
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list

RUN apt-get update -q && apt-get install -y --fix-missing \
    curl \
    nginx \
    php8.3 \
    php8.3-ctype \
    php8.3-curl \
    php8.3-dom \
    php8.3-fileinfo \
    php8.3-fpm \
    php8.3-gd \
    php8.3-intl \
    php8.3-mbstring \
    php8.3-mysqli \
    php8.3-opcache \
    php8.3-pgsql \
    php8.3-phar \
    php8.3-tokenizer \
    php8.3-xml \
    php8.3-xmlreader \
    php8.3-xmlwriter \
    supervisor

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN useradd --shell /bin/bash api

# Copy composer from the composer image
COPY --from=builder /usr/bin/composer /usr/bin/composer

COPY ./config/docker/nginx/default.conf /etc/nginx/sites-available/default
COPY ./config/docker/php/fpm-pool.conf /etc/php/8.3/fpm/pool.d/www.conf
COPY ./config/docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R api:api /var/www/app /run /var/lib/nginx /var/log/nginx

USER api:api

COPY --chown=api:api --from=builder /app/vendor .
COPY --chown=api:api . /var/www/app/

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]