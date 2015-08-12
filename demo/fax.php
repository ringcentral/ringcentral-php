<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

$credentials = require(__DIR__ . '/_credentials.php');

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->getPlatform();

// Authorize

$platform->authorize($credentials['username'], $credentials['extension'], $credentials['password'], true);

// Find Fax-enabled phone number that belongs to extension

$phoneNumbers = $platform->get('/account/~/extension/~/phone-number', array('perPage' => 'max'))
                         ->getJson()->records;


print 'Fax Phone Number: ' . $credentials['username'] . PHP_EOL;

// Send SMS

$request = $rcsdk->getMultipartBuilder()
                 ->setBody(array(
                     'to'         => array(
                         array('phoneNumber' => $credentials['username']),
                     ),
                     'faxResolution' => 'High',
                 ))
                 ->addAttachment('Plain Text', 'file.txt')
                 ->addAttachment(fopen('https://developers.ringcentral.com/assets/images/ico_case_crm.png', 'r'))
                 ->getRequest('/account/~/extension/~/fax');

//print $request->getBody() . PHP_EOL;

$response = $platform->apiCall($request);

print 'Sent Fax ' . $response->getJson()->uri . PHP_EOL;