<?php

use RC\core\ajax\Request;
use RC\core\cache\FileCache;
use RC\core\cache\MemoryCache;
use RC\RCSDK;

$credentials = require('credentials.php');

$cacheDir = __DIR__ . DIRECTORY_SEPARATOR . 'cache';

if (!file_exists($cacheDir)) {
    mkdir($cacheDir);
}

$rcsdkMemory = new RCSDK(new MemoryCache());
$rcsdkFile = new RCSDK(new FileCache($cacheDir));

$main = function (RCSDK $rcsdk) use ($credentials) {

    $platform = $rcsdk->getPlatform();

    $platform->appKey = $credentials['appKey'];
    $platform->appSecret = $credentials['appSecret'];

};

$main($rcsdkMemory);
$main($rcsdkFile);

//////////

$memoryPlatform = $rcsdkMemory->getPlatform();

$auth = $memoryPlatform->authorize($credentials['username'], $credentials['extension'], $credentials['password']);

print 'Memory Authorized' . PHP_EOL;

$refresh = $memoryPlatform->refresh();

print 'Memory Refreshed' . PHP_EOL;

$call = $memoryPlatform->apiCall(new Request('GET', '/account/~/extension/~'));

print 'Memory User loaded ' . $call->getResponse()->getData()['name'] . PHP_EOL;

print '----------' . PHP_EOL;

//////////

$filePlatform = $rcsdkFile->getPlatform();

try {
    $filePlatform->isAuthorized();
    print 'File is authorized already' . PHP_EOL;
} catch (Exception $e) {
    $auth = $filePlatform->authorize($credentials['username'], $credentials['extension'], $credentials['password']);
    print 'File Authorized' . PHP_EOL;
}

$call = $filePlatform->apiCall(new Request('GET', '/account/~/extension/~'));

print 'File User loaded ' . $call->getResponse()->getData()['name'] . PHP_EOL;
