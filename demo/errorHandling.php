<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\http\HttpException;
use RingCentral\http\Response;
use RingCentral\SDK;

$credentials = require(__DIR__ . '/_credentials.php');

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server']);

$platform = $rcsdk->getPlatform();

// Authorize

$platform->authorize($credentials['username'], $credentials['extension'], $credentials['password'], true);

// Load something nonexistent

try {

    $platform->get('/account/~/whatever');

} catch (HttpException $e) {

    $response = $e->getResponse();

    if ($response instanceof Response) { // Response has been received

        $message = $response->getError() . ' (from backend) at URL ' . $e->getRequest()->getUrl();

    } else { // No response received, request failed to start

        $message = $e->getMessage();

    }

    print 'Expected HTTP Error: ' . $message . PHP_EOL;

}
