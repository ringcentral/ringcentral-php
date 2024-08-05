<?php

require_once (__DIR__ . '/_bootstrap.php');

use RingCentral\SDK\SDK;

function errorResponse()
{
    $credentials = require (__DIR__ . '/_credentials.php');
    $accountId = '~';
    $extensionId = '~';
    $queryParams = array(
    );
    $rcsdk = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');

    $platform = $rcsdk->platform();
    $platform->login(["jwt" => $credentials['RC_JWT']]);

    $r = $platform->get("/restapi/v1.0/account/23/extension/{$extensionId}/call-log", $queryParams);

    echo $r->text();

    echo "\n";
}
function successfulResponse()
{
    $credentials = require (__DIR__ . '/_credentials.php');
    $accountId = '~';
    $extensionId = '~';
    $queryParams = array(
    );
    $rcsdk = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');

    $platform = $rcsdk->platform();
    $platform->login(["jwt" => $credentials['RC_JWT']]);

    $r = $platform->get("/restapi/v1.0/account/{$accountId}/extension/{$extensionId}/call-log", $queryParams);

    echo $r->text();

    echo "\n";
}
/*
function errorResponseDeleteMethod()
{
    $credentials = require (__DIR__ . '/_credentials.php');
    $accountId = '~';
    $extensionId = '~';
    $queryParams = array(
        'dateTo' => 'sss'
    );
    $rcsdk = new SDK($credentials['clientId'], $credentials['clientSecret'], $credentials['server'], 'Demo', '1.0.0');

    $platform = $rcsdk->platform();
    $platform->login(["jwt" => $credentials['RC_JWT']]);

    $r = $platform->delete("/restapi/v1.0/account/{$accountId}/extension/{$extensionId}/call-log", $queryParams);

    echo $r->text();

    echo "\n";
}*/
successfulResponse();
errorResponse();