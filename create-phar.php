<?php

$phar = new Phar("./dist/rcsdk.phar",
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
    "rcsdk.phar");

$phar["autoload.php"] = file_get_contents("./lib/autoload.php");
$phar["RC/SDK.php"] = file_get_contents("./lib/RC/SDK.php");
$phar["RC/http/MessageFactory.php"] = file_get_contents("./lib/RC/http/MessageFactory.php");
$phar["RC/http/Response.php"] = file_get_contents("./lib/RC/http/Response.php");
$phar["RC/platform/Auth.php"] = file_get_contents("./lib/RC/platform/Auth.php");
$phar["RC/platform/Platform.php"] = file_get_contents("./lib/RC/platform/Platform.php");

$phar->setStub($phar->createDefaultStub("autoload.php"));

//////////

require('./_cache/guzzle.phar');
require('./dist/rcsdk.phar');

$sdk = new RC\SDK('foo', 'bar', 'http://server');

$url = $sdk->getPlatform()->apiUrl('/foo', ['addServer' => true]);

print $url . ': ' . ($url == 'http://server/restapi/v1.0/foo' ? 'true' : 'false') . PHP_EOL;