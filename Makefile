.PHONY: test
test:
	./vendor/bin/phpunit --configuration ./phpunit.xml

.PHONY: phar
phar:
	php ./create-phar.php ${args}

.PHONY: all
all:
	make test
	make phar

.PHONY: install
install:
	composer install --prefer-source --no-interaction

.PHONY: coveralls
coveralls:
	./vendor/bin/coveralls -v

.PHONY: docker-shell
docker-shell:
	eval "$(boot2docker shellinit)"

.PHONY: docker-login
docker-login:
	docker run -t -i -v $(shell pwd):/opt/sdk ringcentral-php-sdk /bin/bash

.PHONY: docker-build
docker-build:
	docker build -t ringcentral-php-sdk .