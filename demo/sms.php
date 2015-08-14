<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

$credentials = require(__DIR__ . '/_credentials.php');

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->getPlatform();

// Authorize

$platform->authorize($credentials['username'], $credentials['extension'], $credentials['password'], true);

// Find SMS-enabled phone number that belongs to extension

$phoneNumbers = $platform->get('/account/~/extension/~/phone-number', array('perPage' => 'max'))
                         ->getJson()->records;

$smsNumber = null;

foreach ($phoneNumbers as $phoneNumber) {

    if (in_array('SmsSender', $phoneNumber->features)) {

        $smsNumber = $phoneNumber->phoneNumber;

        break;

    }

}

print 'SMS Phone Number: ' . $smsNumber . PHP_EOL;

// Send SMS

if ($smsNumber) {

    $response = $platform
        ->post('/account/~/extension/~/sms', null, array(
            'from' => array('phoneNumber' => $smsNumber),
            'to'   => array(
                array('phoneNumber' => $credentials['mobileNumber']),
            ),
            'text' => 'Test from PHP',
        ));

    print 'Sent SMS ' . $response->getJson()->uri . PHP_EOL;

} else {

    print 'SMS cannot be sent: no SMS-enabled phone number found...' . PHP_EOL;

}

