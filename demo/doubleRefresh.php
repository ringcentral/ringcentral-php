<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

// Create SDK instance

$credentials = require(__DIR__ . '/_credentials.php');

$rcsdk1 = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');
$rcsdk2 = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform1 = $rcsdk1->platform();
$platform2 = $rcsdk2->platform();

$platform1->login($credentials['username'], $credentials['extension'], $credentials['password']);

print 'Platform1 Authorized' . PHP_EOL;

// Share P1 auth data with P2
$platform2->auth()->setData($platform1->auth()->data());

// Make first refresh
$platform1->refresh();

try {

    // Attempt to make second refresh
    $platform2->refresh();

    print 'Tokens should be identical' . PHP_EOL;
    print $platform1->auth()->accessToken() . PHP_EOL;
    print $platform2->auth()->accessToken() . PHP_EOL;

} catch (\Exception $e) {

    print 'Failed to do double refresh: ' . $e->getMessage() . PHP_EOL;
    print $e->getTraceAsString() . PHP_EOL;

    $extension = $platform1->get('/account/~/extension/~')->json();

    print 'Platform1 is still alive though: ' . $extension->name . PHP_EOL;

}