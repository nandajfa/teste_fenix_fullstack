FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
	libpq-dev \
	libzip-dev \
	unzip \
	git \
	&& rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_pgsql zip
RUN pecl install redis pcov \
	&& docker-php-ext-enable redis pcov

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

EXPOSE 8000

CMD ["sh", "/usr/local/bin/entrypoint.sh"]