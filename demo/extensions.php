<?php

require_once(__DIR__ . '/_bootstrap.php');

use RC\SDK;

$credentials = require(__DIR__ . '/_credentials.php');

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server']);

$platform = $rcsdk->getPlatform();

// Authorize

$platform->authorize($credentials['username'], $credentials['extension'], $credentials['password'], true);

// Load extensions

$extensions = $platform->get('/account/~/extension', array('perPage' => 10))->getJson()->records;

print 'Users loaded ' . count($extensions) . PHP_EOL;

// Load presence

$presences = $platform->get('/account/~/extension/' . $extensions[0]->id . ',' . $extensions[0]->id . '/presence')
                      ->getResponses();

print 'Presence loaded ' .
      $extensions[0]->name . ' - ' . $presences[0]->getJson()->presenceStatus . ', ' .
      $extensions[0]->name . ' - ' . $presences[1]->getJson()->presenceStatus . PHP_EOL;

print_r($rcsdk->extension->accountExtensions(['account_id' => '~', 'extension_id' => '~'])->records[0]);
print PHP_EOL;

print_r($rcsdk->account->account(['account_id' => '~']));
print PHP_EOL;

print_r($rcsdk->callLog->accountExtensionCallLogs(['account_id' => '~', 'extension_id' => '~'])->records[0]);
print PHP_EOL;

