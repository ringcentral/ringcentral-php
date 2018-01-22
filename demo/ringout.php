<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;


// Create SDK instance

$credentials = require(__DIR__ . '/_credentials.php');

$rcsdk = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login($credentials['username'], $credentials['extension'], $credentials['password']);

// Make a call

$response = $platform->post('/account/~/extension/~/ringout', array(
    'from' => array('phoneNumber' => $credentials['fromPhoneNumber']),
    'to'   => array('phoneNumber' => $credentials['toPhoneNumber'])
));

$json = $response->json();

$lastStatus = $json->status->callStatus;

// Poll for call status updates

while ($lastStatus == 'InProgress') {

    $current = $platform->get($json->uri);
    $currentJson = $current->json();
    $lastStatus = $currentJson->status->callStatus;
    print 'Status: ' . json_encode($currentJson->status) . PHP_EOL;

    sleep(2);

}

print 'Done.' . PHP_EOL;
