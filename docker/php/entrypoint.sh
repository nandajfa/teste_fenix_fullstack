set -e

composer install --no-interaction --prefer-dist

if [ ! -f .env ]; then
	cp .env.example .env
	php artisan key:generate
fi

php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8000