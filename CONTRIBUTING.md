# Testing

The only thing you need to have for testing is Docker.

Use Docker images to test and collect coverage (as it requires composer and xdebug).

It is more convenient to use included `makefile` scripts:

```bash
$ make docker-build-7
$ make docker-login-7
```

And then inside docker console:

```bash
$ composer update
$ composer install --prefer-dist --no-interaction
$ composer test
$ composer phar         # this is only for CI
$ composer coveralls    # this is only for CI
```

# Links

- https://github.com/zendframework/zend-diactoros
- https://packagist.org/packages/guzzlehttp/psr7
- https://packagist.org/providers/psr/http-message-implementation
