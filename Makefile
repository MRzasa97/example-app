dependencies: 
	docker run --rm -u "$$(id -u):$$(id -g)" -v "$$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs

build:
	./vendor/bin/sail build

up:
	./vendor/bin/sail up -d

down:
	./vendor/bin/sail down

test:
	./vendor/bin/sail test --coverage

migrate:
	./vendor/bin/sail artisan migrate