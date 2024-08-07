<?php

require_once (__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;
use RingCentral\SDK\Subscription\Events\NotificationEvent;
use RingCentral\SDK\Subscription\Events\SuccessEvent;
use RingCentral\SDK\Subscription\PubnubSubscription;


$credentials = require ('_credentials.php');


$credentials = require (__DIR__ . '/_credentials.php');

$rcsdk = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login(["jwt" => $credentials['RC_JWT']]);

// Subscription

$subscription = $rcsdk->createSubscription('Pubnub');

$subscription->addEvents(
    array(
        '/account/~/extension/~/message-store',
        '/account/~/extension/~/presence'
    )
);

$subscription->setKeepPolling(true);

$subscription->addListener(PubnubSubscription::EVENT_NOTIFICATION, function (NotificationEvent $e) {
    print 'Notification' . print_r($e->payload(), true) . PHP_EOL;
});

print 'Subscribing' . PHP_EOL;

$subscription->addListener(PubnubSubscription::EVENT_TIMEOUT, function () {
    print 'Timeout' . PHP_EOL;
});

$subscription->addListener(PubnubSubscription::EVENT_RENEW_SUCCESS, function (SuccessEvent $e) {
    print 'Renewed' . PHP_EOL;
});

$subscription->register();

print 'End' . PHP_EOL;
