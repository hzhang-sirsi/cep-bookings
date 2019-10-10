FROM ubuntu:bionic

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    build-essential \
    git \
    wget \
    unzip \
    php7.2 \
    php7.2-mbstring \
    php7.2-dom

RUN useradd -ms /bin/bash build
USER build:build

COPY --chown=build:build . /opt/build/
WORKDIR /opt/build

CMD ["bash", "-c", "make && env/composer install && make build && cp build/* /out"]
