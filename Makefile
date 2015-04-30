.PHONY: test
test:
	./vendor/bin/phpunit --configuration ./phpunit.xml --colors --coverage-html ./dist/coverage

.PHONY: phar
phar:
	php ./create-phar.php

.PHONY: all
all:
	make test
	make phar

.PHONY: install
install:
	composer install --prefer-source --no-interaction