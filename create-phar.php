<?php

try {
    require(__DIR__ . '/dist/ringcentral.phar');

    $sdk = new RingCentral\SDK\SDK('xxx', 'xxx', 'https://platform.ringcentral.com');

    $t = $sdk->platform()->get('/restapi/v1.0', array(), array(), array('skipAuthCheck' => true));

    print 'Connected to API server ' . $t->json()->uri . ', version ' . $t->json()->versionString . PHP_EOL;

} catch (Exception $e) {
    print 'Error while connecting using PHAR: ' . $e->getMessage();
    exit(1);
}