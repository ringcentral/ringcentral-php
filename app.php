<?php

use RC\SDK;

//////////

$cacheDir = __DIR__ . DIRECTORY_SEPARATOR . '_cache';
$file = $cacheDir . DIRECTORY_SEPARATOR . 'platform.json';

if (!file_exists($cacheDir)) {
    mkdir($cacheDir);
}

$cachedAuth = file_exists($file) ? json_decode(file_get_contents($file)) : null;

$credentials = require('credentials.php');

//////////

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server']);

$platform = $rcsdk->getPlatform();

$platform->setAuthData($cachedAuth);

try {

    $platform->isAuthorized();

    print 'Authorization was restored' . PHP_EOL;

} catch (Exception $e) {

    print 'Exception: ' . $e->getMessage() . PHP_EOL;

    $auth = $platform->authorize($credentials['username'], $credentials['extension'], $credentials['password']);

    print 'Authorized' . PHP_EOL;

}

$refresh = $platform->refresh();

print 'Refreshed' . PHP_EOL;

$extensions = $platform->get('/account/~/extension', ['perPage' => 10])
                       ->getData()->records;

print 'Users loaded ' . count($extensions) . PHP_EOL;

$presences = $platform->get('/account/~/extension/' . $extensions[0]->id . ',' . $extensions[1]->id . '/presence')
                      ->getResponses();

print 'Presence loaded ' .
      $extensions[0]->name . ' - ' . $presences[0]->getData()->presenceStatus . ', ' .
      $extensions[1]->name . ' - ' . $presences[1]->getData()->presenceStatus . PHP_EOL;

//////////

file_put_contents($file, json_encode($platform->getAuthData(), JSON_PRETTY_PRINT));