<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;


// Create SDK instance

$credentials = require(__DIR__ . '/_credentials.php');

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login($credentials['username'], $credentials['extension'], $credentials['password']);

// Find SMS-enabled phone number that belongs to extension

$response = $platform->post('/subscription', array(
    'eventFilters' => array(
        '/restapi/v1.0/account/~/extension/~/message-store',
        '/restapi/v1.0/account/~/extension/~/presence'
    ),
    'deliveryMode' => array(
        'transportType' => 'WebHook',
        'address' => $credentials['webhookUri']
    )
));

print 'Webhook Subscription ' . $response->json() . PHP_EOL;
