<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK;

$credentials = require(__DIR__ . '/_credentials.php');

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server']);

$platform = $rcsdk->getPlatform();

// Authorize

$platform->authorize($credentials['username'], $credentials['extension'], $credentials['password'], true);

// Make a call

$response = $platform->post('/account/~/extension/~/ringout', null, array(
    'from' => array('phoneNumber' => '15551112233'),
    'to'   => array('phoneNumber' => $credentials['mobileNumber'])
));

$json = $response->getJson();

$lastStatus = $json->status->callStatus;

// Poll for call status updates

while ($lastStatus == 'InProgress') {

    $current = $platform->get($json->uri);
    $currentJson = $current->getJson();
    $lastStatus = $currentJson->status->callStatus;
    print 'Status: ' . json_encode($currentJson->status) . PHP_EOL;

    sleep(2);

}

print 'Done.' . PHP_EOL;
