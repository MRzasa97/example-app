Project is created in laravel and contenerization is achived by sail
https://laravel.com/docs/11.x/sail

To run this project you have to have docker and make installed:
https://docs.docker.com/get-docker/
sudo apt-get update
sudo apt-get -y install make

To build this project you firstly have to run command that will install all composer dependencies:
    ``

then:<br />
    `make build`<br />
    `make up`<br />
    `make migrate`<br />

to run tests you can use: <br />
    `make test`<br />

You can also used commands assigned to make commands in Makefile.

dependencies: <br />
`    docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs`

build:<br />
	`./vendor/bin/sail build`

up: <br />
	`./vendor/bin/sail up -d`

down: <br />
	`./vendor/bin/sail down`

test: <br />
	`./vendor/bin/sail test --coverage`

migrate: <br />
	`./vendor/bin/sail artisan migrate`

If you want to upload file, firstly you have to get token from `/token/` endpoint.
This endpoint should be secured, but I wanted to focus on core logic and send project in time.