FROM php:8.3-fpm



RUN apt-get update && \
    apt-get install -y \
        curl vim unzip \
        libyaml-dev libmagickwand-dev libzip-dev \
        libssl-dev libcurl4-openssl-dev pkg-config \
        libidn-dev && \
    pecl install raphf && \
    docker-php-ext-enable raphf && \
    pecl install propro || true && \
    docker-php-ext-enable propro || true && \
    pecl install yaml imagick && \
    docker-php-ext-enable yaml imagick && \
    pecl install pecl_http && \
    docker-php-ext-enable http && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pcntl

RUN apt-get update && apt-get install -y libyaml-dev \
    && pecl install yaml \
    && docker-php-ext-enable yaml

# Standard CMD
CMD ["php", "-a"]
