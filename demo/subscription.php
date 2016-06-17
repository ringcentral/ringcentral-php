<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;
use RingCentral\SDK\Subscription\Events\NotificationEvent;
use RingCentral\SDK\Subscription\Subscription;


// Create SDK instance

$credentials = require(__DIR__ . '/_credentials.php');

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login($credentials['username'], $credentials['extension'], $credentials['password']);

// Subscription

$subscription = $rcsdk->createSubscription();

$subscription->addEvents(array(
	'/account/~/extension/~/message-store',
	'/account/~/extension/~/presence'));

$subscription->setKeepPolling(true);

$subscription->addListener(Subscription::EVENT_NOTIFICATION, function (NotificationEvent $e) {
    print 'Notification' . print_r($e->payload(), true) . PHP_EOL;
});

print 'Subscribing' . PHP_EOL;

$subscription->register();

print 'End' . PHP_EOL;

while (1>0) {
	sleep(1);
}
