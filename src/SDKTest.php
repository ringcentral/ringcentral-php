<?php

use GuzzleHttp\Psr7\Request;
use RingCentral\SDK\SDK;
use RingCentral\SDK\Test\TestCase;

class SDKTest extends TestCase
{
    public function testConstructor()
    {
        $sdk = new SDK('foo', 'bar', 'baz');
        $this->assertNotEquals($sdk->getPlatform(), null);
    }

    private function connectToLiveServer($server)
    {
        $sdk = new SDK('foo', 'bar', $server);
        $platform = $sdk->getPlatform();
        $res = $platform->apiCall(new Request('GET', ''), false)->getJson();
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
        $this->getSDK(false)->getMultipartBuilder();
    }

}