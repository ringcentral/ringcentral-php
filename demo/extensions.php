<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

$credentials = require(__DIR__ . '/_credentials.php');

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->getPlatform();

// Authorize

$platform->authorize($credentials['username'], $credentials['extension'], $credentials['password'], true);

// Load extensions

$extensions = $platform->get('/account/~/extension', array('perPage' => 10))->getJson()->records;

print 'Users loaded ' . count($extensions) . PHP_EOL;

// Load presence

$presences = $platform->get('/account/~/extension/' . $extensions[0]->id . ',' . $extensions[0]->id . '/presence')
                      ->getMultipart();

print 'Presence loaded ' .
      $extensions[0]->name . ' - ' . $presences[0]->getJson()->presenceStatus . ', ' .
      $extensions[0]->name . ' - ' . $presences[1]->getJson()->presenceStatus . PHP_EOL;

print_r($platform->get('/account/~/extension', array('perPage' => 10))->getRequest());