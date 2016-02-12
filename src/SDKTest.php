<?php

use GuzzleHttp\Psr7\Request;
use RingCentral\SDK\SDK;
use RingCentral\SDK\Test\TestCase;

class SDKTest extends TestCase
{
    public function testConstructor()
    {
        $sdk = new SDK('foo', 'bar', 'baz', 'SDKTests', SDK::VERSION);
        $this->assertNotEquals($sdk->platform(), null);
    }

    private function connectToLiveServer($server)
    {

        $sdk = new SDK('foo', 'bar', $server);

        $res = $sdk->platform()
            ->get('', array(), array(), array('skipAuthCheck' => true))
            ->json();

        $this->assertEquals('v1.0', $res->uriString);

    }

    public function testProduction()
    {
        $this->connectToLiveServer(SDK::SERVER_PRODUCTION);
    }

    public function testSandbox()
    {
        $this->connectToLiveServer(SDK::SERVER_SANDBOX);
    }

    public function testMultipartBuilder()
    {
        $this->getSDK(false)->createMultipartBuilder();
    }

}