<?php

namespace RingCentral\test;

use PHPUnit_Framework_TestCase;
use RingCentral\http\mocks\AuthenticationMock;
use RingCentral\SDK;

class TestCase extends PHPUnit_Framework_TestCase
{

    protected function getSDK($authorized = true)
    {

        date_default_timezone_set('UTC');

        $sdk = new SDK('whatever', 'whatever', 'https://whatever');

        $context = $sdk->getContext();

        $context
            ->usePubnubStub(true)
            ->useRequestStub(true);

        if ($authorized) {

            $context
                ->getMocks()
                ->add(new AuthenticationMock());

            $sdk->getPlatform()->authorize('18881112233', null, 'password', true);

        }

        return $sdk;

    }

}