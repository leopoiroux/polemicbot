FROM php:8.1-cli

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get update && apt-get install git zip unzip -y

COPY . .

RUN composer install --prefer-dist --optimize-autoloader --no-dev --no-interaction && composer clear-cache

CMD [ "php", "./src/app.php" ]
