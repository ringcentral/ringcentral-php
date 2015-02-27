<?php

require_once('lib/autoload.php');

date_default_timezone_set('UTC');

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(function (Exception $e) {

    print 'Exception: ' . $e->getMessage() . PHP_EOL;

    if ($e instanceof \RC\ajax\AjaxException) {
        print 'AJAX Data:' . PHP_EOL;
        print_r($e->getAjax()->getResponse()->getData());
        print PHP_EOL;
    }

    print $e->getTraceAsString() . PHP_EOL;

    if ($e->getPrevious()) {
        print 'Previous: ' . $e->getMessage() . PHP_EOL;
        print $e->getPrevious()->getTraceAsString() . PHP_EOL;
    }

});

require_once('app.php');