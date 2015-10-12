<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

$credentials = require(__DIR__ . '/_credentials.php');

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login($credentials['username'], $credentials['extension'], $credentials['password'], true);

// Find call log records with recordings

$callLogRecords = $platform->get('/account/~/extension/~/call-log', array(
                             'type'          => 'Voice',
                             'withRecording' => 'True'))
                           ->json()->records;

foreach ($callLogRecords as $callLogRecord) {

    $id = $callLogRecord->recording->id;
    
    print "Downloading Call Log Record ${id}" . PHP_EOL;

    $uri = $callLogRecord->recording->contentUri;

    print "Retrieving ${uri}" . PHP_EOL;

    $apiResponse = $platform->get($callLogRecord->recording->contentUri);
    
    $ext = ($apiResponse->response()->getHeader('Content-Type')[0] == 'audio/mpeg')
      ? 'mp3' : 'wav';

    file_put_contents("recording_${id}.${ext}", $apiResponse->raw());

    print "Wrote Recording for Call Log Record ${id}" . PHP_EOL;

    file_put_contents("recording_${id}.json", json_encode($callLogRecord));

    print "Wrote Metadata for Call Log Record ${id}" . PHP_EOL;

}

?>
