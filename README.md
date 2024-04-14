Project is created in laravel and contenerization is achived by sail
https://laravel.com/docs/11.x/sail

To run this project you have to have docker and make installed:
https://docs.docker.com/get-docker/
sudo apt-get update
sudo apt-get -y install make

To build this project you firstly have to run command that will install all composer dependencies:
    make dependencies

then:
    make build
    make up
    make migrate

to run tests you can use:
    make test

You can also used commands assigned to make commands in Makefile.