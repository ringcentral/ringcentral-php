<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;
use RingCentral\SDK\WebSocket\WebSocket;
use RingCentral\SDK\WebSocket\Subscription;
use RingCentral\SDK\WebSocket\Events\NotificationEvent;
use RingCentral\SDK\WebSocket\Events\SuccessEvent;
use RingCentral\SDK\WebSocket\Events\ErrorEvent;
use React\EventLoop\Loop;

$credentials = require('_credentials.php');


$credentials = require(__DIR__ . '/_credentials.php');

$rcsdk = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login($credentials);

// WebSocket

$websocket = $rcsdk->initWebSocket();

$websocket->addListener(WebSocket::EVENT_READY, function (SuccessEvent $e) {
    print 'Websocket Ready' . PHP_EOL;
    print 'Connection Details' . print_r($e->apiResponse()->body(), true) . PHP_EOL;
});

$websocket->addListener(WebSocket::EVENT_ERROR, function (ErrorEvent $e) {
    print 'Websocket Error' . PHP_EOL;
});

$websocket->connect();

// Subscription

print 'Subscribing' . PHP_EOL;
$subscription = $rcsdk->createSubscription();

$subscription->addEvents(array(
    '/restapi/v1.0/account/~/extension/~/presence'
));

$subscription->addListener(Subscription::EVENT_NOTIFICATION, function (NotificationEvent $e) {
    print 'Notification ' . print_r($e->payload(), true) . PHP_EOL;
});

// $subscription->addListener(Subscription::EVENT_TIMEOUT, function () {
//     print 'Timeout' . PHP_EOL;
// });

$subscription->addListener(Subscription::EVENT_SUBSCRIBE_SUCCESS, function (SuccessEvent $e) {
    print 'Subscribed ' . print_r($e->apiResponse()->body(), true) . PHP_EOL;
});

$subscription->addListener(Subscription::EVENT_SUBSCRIBE_ERROR, function (ErrorEvent $e) {
    print 'Subscribe error ' . print_r($e->exception()->apiResponse()->body(), true) . PHP_EOL;
});

$subscription->addListener(Subscription::EVENT_RENEW_SUCCESS, function (SuccessEvent $e) {
    print 'Renewed ' . print_r($e->apiResponse()->body(), true) . PHP_EOL;
});

$subscription->addListener(Subscription::EVENT_RENEW_ERROR, function (ErrorEvent $e) {
    print 'Renew error ' . print_r($e->exception()->apiResponse()->body(), true) . PHP_EOL;
});

$subscription->register();

print 'Listening' . PHP_EOL;

Loop::addTimer(10.0, function () use ($subscription) {
    print 'Adding new events' . PHP_EOL;
    $subscription->addEvents(array(
        '/restapi/v1.0/account/~/extension/~/message-store/instant?type=SMS'
    ));
    $subscription->register();
});
