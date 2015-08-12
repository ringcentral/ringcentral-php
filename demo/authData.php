<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

// Create SDK instance

$credentials = require(__DIR__ . '/_credentials.php');

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->getPlatform();

// Retrieve previous authentication data

$cacheDir = __DIR__ . DIRECTORY_SEPARATOR . '_cache';
$file = $cacheDir . DIRECTORY_SEPARATOR . 'platform.json';

if (!file_exists($cacheDir)) {
    mkdir($cacheDir);
}

$cachedAuth = array();

if (file_exists($file)) {
    $cachedAuth = json_decode(file_get_contents($file), true);
    unlink($file); // dispose cache file, it will be updated if script ends successfully
}

$platform->setAuthData($cachedAuth);

try {

    $platform->isAuthorized();

    print 'Authorization was restored' . PHP_EOL;

} catch (Exception $e) {

    print 'Auth exception: ' . $e->getMessage() . PHP_EOL;

    $auth = $platform->authorize($credentials['username'], $credentials['extension'], $credentials['password'], true);

    print 'Authorized' . PHP_EOL;

}

// Save authentication data

file_put_contents($file, json_encode($platform->getAuthData(), JSON_PRETTY_PRINT));
