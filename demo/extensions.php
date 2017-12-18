<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

// Create SDK instance

$credentials = require(__DIR__ . '/_credentials.php');

$rcsdk = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login($credentials['username'], $credentials['extension'], $credentials['password']);

// Load extensions

$extensions = $platform->get('/account/~/extension', array('perPage' => 10))->json()->records;

print 'Users loaded ' . count($extensions) . PHP_EOL;

// Load presence

$presences = $platform->get('/account/~/extension/' . $extensions[0]->id . ',' . $extensions[0]->id . '/presence')
                      ->multipart();

print 'Presence loaded ' .
      $extensions[0]->name . ' - ' . $presences[0]->json()->presenceStatus . ', ' .
      $extensions[0]->name . ' - ' . $presences[1]->json()->presenceStatus . PHP_EOL;

//print_r($platform->get('/account/~/extension', array('perPage' => 10))->request());