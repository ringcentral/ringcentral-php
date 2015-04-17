<?php

require(__DIR__ . '/demo/authData.php');
require(__DIR__ . '/demo/errorHandling.php');
require(__DIR__ . '/demo/extensions.php');

if (!$argv || !in_array('skipSMS', $argv)) {
    require(__DIR__ . '/demo/sms.php');
}

if (!$argv || !in_array('skipRingOut', $argv)) {
    require(__DIR__ . '/demo/ringout.php');
}

require(__DIR__ . '/demo/subscription.php');