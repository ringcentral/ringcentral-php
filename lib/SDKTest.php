<?php

use RingCentral\SDK;

class SDKTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $sdk = new SDK('foo', 'bar', 'baz');
        $this->assertNotEquals($sdk->getPlatform(), null);
    }
}