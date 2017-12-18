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

    if (in_array('MmsSender', $phoneNumber->features)) {

        $smsNumber = $phoneNumber->phoneNumber;

        break;

    }

}

print 'MMS Phone Number: ' . $smsNumber . PHP_EOL;

// Send Fax

$request = $rcsdk->createMultipartBuilder()
                 ->setBody(array(
                     'from' => array('phoneNumber' => $smsNumber),
                     'to'   => array(
                         array('phoneNumber' => $credentials['mobileNumber']),
                     ),
                 ))
                 ->add(fopen('https://developers.ringcentral.com/assets/images/ico_case_crm.png', 'r'))
                 ->request('/account/~/extension/~/sms');

//print $request->getBody() . PHP_EOL;

$response = $platform->sendRequest($request);

print 'Sent MMS ' . $response->json()->uri . PHP_EOL;