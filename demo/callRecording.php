<?php

require_once(__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

$credentials_file = count($argv) > 1 
  ? $argv[1] : __DIR__ . '/_credentials.json';

$credentials = json_decode(file_get_contents($credentials_file), true);

// Create SDK instance

$rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server'], 'Demo', '1.0.0');

$platform = $rcsdk->platform();

// Authorize

$platform->login($credentials['username'], $credentials['extension'], $credentials['password'], true);

// Find call log records with recordings

$callLogRecords = $platform->get('/account/~/extension/~/call-log', array(
                             'type'          => 'Voice',
                             'withRecording' => 'True',
                             'dateFrom'      => $credentials['dateFrom'],
                             'dateTo'        => $credentials['dateTo']))
                           ->json()->records;

// Create a CSV file to log the records

  $status = "Success";
  $dir = $credentials['dateFrom'];
  $fname = "recordings_${dir}.csv";
  $fdir = "/DownloadRecordings/Recordings/${dir}";

  // check if the directory exists
  // mkdir('/AllianceRecordings/Recordings/${dir}', 0700);

  if (is_dir($fdir) === false)
  {
    mkdir($fdir, 0777, true);
    // mkdir("/AllianceRecordings/JSON/${dir}", 0755, true);
  }

  $file = fopen($fname,'w');
  $fileHeaders = array("RecordingID","ContentURI","Filename","DownloadStatus");
  fputcsv($file, $fileHeaders);
  $fileContents = array();


  

  $timePerRecording = 6;
  
  foreach ($callLogRecords as $i => $callLogRecord) {

    $id = $callLogRecord->recording->id;
    
    print "Downloading Call Log Record ${id}" . PHP_EOL;

    $uri = $callLogRecord->recording->contentUri;

    print "The contentURI is : ${uri}";

    print "Retrieving ${uri}" . PHP_EOL;

    $apiResponse = $platform->get($callLogRecord->recording->contentUri);
    
    $ext = ($apiResponse->response()->getHeader('Content-Type')[0] == 'audio/mpeg')
      ? 'mp3' : 'wav';

    $start = microtime(true);

    file_put_contents("${fdir}/recording_${id}.${ext}", $apiResponse->raw());

    $filename = "recording_${id}.${ext}";

    if(filesize("${fdir}/recording_${id}.${ext}") == 0) {
        $status = "failure";
    }

    print "Wrote Recording for Call Log Record ${id}" . PHP_EOL;

    file_put_contents("${fdir}/recording_${id}.json", json_encode($callLogRecord));

    print "Wrote Metadata for Call Log Record ${id}" . PHP_EOL;

    $end=microtime(true);

    // Check if the recording completed wihtin 6 seconds.
    $time = ($end*1000 - $start * 1000);
    if($time < $timePerRecording) {
      sleep($timePerRecording-$time);
    }

   
    $fileContents = array($id, $uri, $filename, $status);
    fputcsv($file, $fileContents);

  }

  fclose($file);

?>
