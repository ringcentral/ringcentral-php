<?php

print "Test 1: authData.php\n";
require(__DIR__ . '/demo/authData.php');

print "Test 2: errorHandling.php\n";
require(__DIR__ . '/demo/errorHandling.php');

print "Test 3: extensions.php\n";
require(__DIR__ . '/demo/extensions.php');

if (!$argv || !in_array('skipSMS', $argv)) {
	print "Test 4: sms.php\n";
    require(__DIR__ . '/demo/sms.php');
} else {
	print "Test 4: sms.php - skipping...\n";
}

if (!$argv || !in_array('skipMMS', $argv)) {
	print "Test 5: mms.php\n";
    require(__DIR__ . '/demo/mms.php');
} else {
	print "Test 5: mms.php - skipping...\n";
}

if (!$argv || !in_array('skipRingOut', $argv)) {
	print "Test 6: ringout.php\n";
    require(__DIR__ . '/demo/ringout.php');
} else {
	print "Test 6: ringout.php - skipping...\n";
}

print "Test 7: subscription.php\n";
require(__DIR__ . '/demo/subscription.php');