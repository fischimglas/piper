FROM php:8.3-fpm



RUN apt-get update && \
    apt-get install -y \
        curl vim unzip \
        libyaml-dev libmagickwand-dev libzip-dev \
        libssl-dev libcurl4-openssl-dev pkg-config \
        libidn-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pcntl

## TODO: this fails sometimes. Comment out, build, run command in container, then uncomment. works for now.
RUN apt-get update && apt-get install -y libyaml-dev
RUN pecl install yaml && docker-php-ext-enable yaml
RUN pecl install raphf && docker-php-ext-enable raphf
RUN pecl install propro || true && docker-php-ext-enable propro || true
RUN pecl install yaml imagick && docker-php-ext-enable yaml imagick
RUN pecl install pecl_http && docker-php-ext-enable http

# Standard CMD
CMD ["php", "-a"]
