FROM ubuntu:20.10
COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV DEBIAN_FRONTEND noninteractive

VOLUME ["/app"]
WORKDIR /app

RUN apt-get update \
&& apt-get install -y curl software-properties-common \
&& add-apt-repository ppa:ondrej/php \
&& apt-get update \
&& apt-get upgrade -y \
&& apt-get install -y \
    git \
    unzip \
    php7.4 \
    php7.4-cli \
    php7.4-curl \
    php7.4-json \
    php7.4-xml \
    php7.4-zip

ENTRYPOINT while true; do sleep 30; done
