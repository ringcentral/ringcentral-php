<?php

namespace RingCentral\SDK\Test;

use \PHPUnit_Framework_TestCase;
use RingCentral\SDK\Mocks\AuthenticationMock;
use RingCentral\SDK\SDK;

abstract class TestCase extends PHPUnit_Framework_TestCase
{

    protected function getSDK($authorized = true)
    {

        date_default_timezone_set('UTC');

        $sdk = new SDK('whatever', 'whatever', 'https://whatever', 'SDKTests', SDK::VERSION, $authorized, $authorized);

        if ($authorized) {

            $sdk->mockRegistry()->add(new AuthenticationMock());

            $sdk->platform()->login('18881112233', null, 'password');

        }

        return $sdk;

    }

}