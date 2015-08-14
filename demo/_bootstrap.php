<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use RingCentral\SDK\Http\HttpException;

date_default_timezone_set('UTC');

// Make all PHP errors to be thrown as Exceptions

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Set up global exception handler (this includes Guzzle HTTP Exceptions)

set_exception_handler(function (Exception $e) {

    print 'Exception: ' . $e->getMessage() . PHP_EOL;

    if ($e instanceof HttpException) {

        print 'SDK HTTP Error at ' . $e->getTransaction()->getRequest()->getUri() . PHP_EOL .
              'Response body: ' . $e->getTransaction()->getText() . PHP_EOL;

    }

    if ($e->getPrevious()) {
        print 'Previous: ' . $e->getPrevious()->getMessage() . PHP_EOL;
    }

    print $e->getTraceAsString() . PHP_EOL;

});
