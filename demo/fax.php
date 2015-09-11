<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

$credentials = require(__DIR__ . '/_credentials.php');

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login($credentials['username'], $credentials['extension'], $credentials['password']);

// Find Fax-enabled phone number that belongs to extension

$phoneNumbers = $platform->get('/account/~/extension/~/phone-number', array('perPage' => 'max'))
                         ->json()->records;


print 'Fax Phone Number: ' . $credentials['username'] . PHP_EOL;

// Send Fax

$request = $rcsdk->createMultipartBuilder()
                 ->setBody(array(
                     'to'         => array(
                         array('phoneNumber' => $credentials['username']),
                     ),
                     'faxResolution' => 'High',
                 ))
                 ->add('Plain Text', 'file.txt')
                 ->add(fopen('https://developers.ringcentral.com/assets/images/ico_case_crm.png', 'r'))
                 ->request('/account/~/extension/~/fax');

//print $request->getBody() . PHP_EOL;

$response = $platform->sendRequest($request);

print 'Sent Fax ' . $response->json()->uri . PHP_EOL;