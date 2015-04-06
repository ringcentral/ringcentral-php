<?php

use RC\http\Headers;
use RC\http\mocks\AuthenticationResponse;
use RC\SDK;

class PlatformTest extends PHPUnit_Framework_TestCase
{

    protected function getSDK(){

        $sdk = new SDK('foo', 'bar', 'baz');

        $sdk->getContext()
            ->usePubnubStub(true)
            ->useRequestStub(true);

        return $sdk;

    }

    public function testLogin()
    {

        $sdk = $this->getSDK();

        $sdk->getContext()->getResponseMockCollection()->add(new AuthenticationResponse());

        $sdk->getPlatform()->authorize('foo', null, 'baz', true);

        $this->assertTrue(!empty($sdk->getPlatform()->getAuthData()['remember']));

    }

}