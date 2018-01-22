<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;


// Create SDK instance

$credentials = require(__DIR__ . '/_credentials.php');

$rcsdk = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login($credentials['username'], $credentials['extension'], $credentials['password']);

// Find SMS-enabled phone number that belongs to extension

$phoneNumbers = $platform->get('/account/~/extension/~/phone-number', array('perPage' => 'max'))->json()->records;

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
        ->post('/account/~/extension/~/sms', array(
            'from' => array('phoneNumber' => $smsNumber),
            'to'   => array(
                array('phoneNumber' => $credentials['mobileNumber']),
            ),
            'text' => 'Test from PHP',
        ));

    print 'Sent SMS ' . $response->json()->uri . PHP_EOL;

} else {

    print 'SMS cannot be sent: no SMS-enabled phone number found...' . PHP_EOL;

}

