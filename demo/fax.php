<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK;

$credentials = require(__DIR__ . '/_credentials.php');

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server']);

$platform = $rcsdk->getPlatform();

// Authorize

$platform->authorize($credentials['username'], $credentials['extension'], $credentials['password'], true);

// Find Fax-enabled phone number that belongs to extension

$phoneNumbers = $platform->get('/account/~/extension/~/phone-number', array('perPage' => 'max'))
                         ->getJson()->records;

$faxNumber = null;

print_r($phoneNumbers[0]);

foreach ($phoneNumbers as $phoneNumber) {

    if (stristr($phoneNumber->type, 'Fax')) {

        $faxNumber = $phoneNumber->phoneNumber;

        break;

    }

}

print 'Fax Phone Number: ' . $faxNumber . PHP_EOL;

// Send SMS

if ($faxNumber) {

    $request = $rcsdk->getMultipartBuilder()
                     ->setBody(array(
                         'to'         => array(
                             array('phoneNumber' => $faxNumber),
                         ),
                         'faxResolution' => 'High',
                     ))
                     ->addAttachment('Plain Text')
                     ->addAttachment(fopen('https://developers.ringcentral.com/assets/images/img_api.png', 'r'))
                     ->getRequest('/account/~/extension/~/fax');

    $response = $platform->apiCall($request);

    print 'Sent Fax ' . $response->getJson()->uri . PHP_EOL;

} else {

    print 'Fax cannot be sent: no Fax-enabled phone number found...' . PHP_EOL;

}