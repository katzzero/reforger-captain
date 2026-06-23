FROM php:8.3-cli-alpine

RUN apk add --no-cache docker-cli linux-headers && docker-php-ext-install sockets

COPY index.php api.php style.css /app/
WORKDIR /app

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080"]
