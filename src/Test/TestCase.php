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

        $sdk = new SDK('whatever', 'whatever', 'https://whatever');

        if ($authorized) {

            $sdk->getPubnubFactory()->useMock(true);
            $sdk->getClient()->useMock(true)->getMockRegistry()->add(new AuthenticationMock());

            $sdk->getPlatform()->authorize('18881112233', null, 'password', true);

        }

        return $sdk;

    }

}