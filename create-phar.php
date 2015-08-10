<?php

exec('rm -rf ' . __DIR__ . '/dist/phar');

@mkdir('./dist/phar');
@unlink('./dist/ringcentral.phar');
@unlink('./dist/phar/composer.json');
@unlink('./dist/phar/composer.lock');

$phar = new Phar(
    './dist/ringcentral.phar',
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
    'ringcentral.phar'
);

function listDir($root, $path, $phar)
{

    //print 'Entering ' . $root . $path . PHP_EOL;

    $it = new DirectoryIterator($root . $path);

    foreach ($it as $fileinfo) {

        $filename = $fileinfo->getFilename();

        if ($fileinfo->isDot() ||
            stristr($filename, 'Test.php') ||
            stristr($filename, '.git') ||
            stristr($filename, 'manual_tests') ||
            stristr($filename, 'tests') ||
            stristr($filename, 'demo') ||
            stristr($filename, 'dist') ||
            stristr($filename, 'docs')
        ) {

            continue;

        } elseif ($fileinfo->isDir()) {

            listDir($root, $path . '/' . $filename, $phar);

        } else {

            $key = ($path ? $path . '/' : '') . $filename;

            $phar[$key] = file_get_contents($root . $path . '/' . $fileinfo->getFilename());

            //print '  ' . $key . ' -> ' . $path . '/' . $filename . PHP_EOL;

        }
    }

}

file_put_contents('./dist/phar/composer.json', json_encode(array(
    "type"              => "project",
    "minimum-stability" => "dev",
    "repositories"      => array(
        array(
            "url"  => __DIR__,
            "type" => "vcs"
        )
    ),
    "require"           => array(
        "ringcentral/ringcentral-php" => "dev-develop"
    )
)));

exec('cd ' . __DIR__ . '/dist/phar && composer install --prefer-source --no-interaction --no-dev');

listDir(__DIR__ . '/dist/phar/vendor', '', $phar);

$phar->setStub($phar->createDefaultStub("autoload.php"));

/////

require('./dist/ringcentral.phar');

$credentials = require('demo/_credentials.php');

$sdk = new RingCentral\SDK\SDK($credentials['appKey'], $credentials['appSecret'], $credentials['server']);

$sdk->getPlatform()->authorize($credentials['username'], $credentials['extension'], $credentials['password'], true);

$t = $sdk->getPlatform()->get('/restapi/v1.0');

print 'Connected to API server ' . $t->getJson()->uri . ', version ' . $t->getJson()->versionString . PHP_EOL;