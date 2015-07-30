<?php

namespace RingCentral\test;

use PHPUnit_Framework_TestCase;
use RingCentral\mocks\AuthenticationMock;
use RingCentral\SDK;

abstract class TestCase extends PHPUnit_Framework_TestCase
{

    protected function getSDK($authorized = true)
    {

        date_default_timezone_set('UTC');

        $sdk = new SDK('whatever', 'whatever', 'https://whatever');

        if ($authorized) {

            $sdk->getPubnubFactory()->setUseMock(true);
            $sdk->getClient()->setUseMock(true)->getMockRegistry()->add(new AuthenticationMock());

            $sdk->getPlatform()->authorize('18881112233', null, 'password', true);

        }

        return $sdk;

    }

}