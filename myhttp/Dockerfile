FROM php:7.2-cli
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
RUN docker-php-ext-install sockets
ENTRYPOINT ["/usr/local/bin/php", "index.php"]

