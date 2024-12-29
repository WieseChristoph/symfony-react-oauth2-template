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

FROM alpine AS runner

WORKDIR /var/www/app

RUN apk add --no-cache \
  curl \
  nginx \
  php84 \
  php84-ctype \
  php84-curl \
  php84-dom \
  php84-fileinfo \
  php84-fpm \
  php84-gd \
  php84-intl \
  php84-mbstring \
  php84-mysqli \
  php84-opcache \
  php84-openssl \
  php84-phar \
  php84-session \
  php84-tokenizer \
  php84-xml \
  php84-xmlreader \
  php84-xmlwriter \
  supervisor \
  composer

COPY ./config/docker/nginx/default.conf /etc/nginx/http.d/

COPY ./config/docker/php/fpm-pool.conf /etc/php84/php-fpm.d/www.conf

COPY ./config/docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R nobody:nobody /var/www/app /run /var/lib/nginx /var/log/nginx /var/log/php84

USER nobody

COPY --chown=nobody --from=builder /app/vendor .
COPY --chown=nobody . /var/www/app/

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]