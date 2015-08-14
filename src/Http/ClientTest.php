<?php

use GuzzleHttp\Psr7\Request;
use RingCentral\SDK\Http\Client;
use RingCentral\SDK\Test\TestCase;

class ClientTest extends TestCase
{

    public function testRequestFactory()
    {

        $client = new Client();

        // Query string

        $r = $client->requestFactory('GET', 'http://whatever:8080/path', array('foo' => 'bar', 'baz' => 'qux'));

        $this->assertEquals('http', $r->getUri()->getScheme());
        $this->assertEquals('whatever', $r->getUri()->getHost());
        $this->assertEquals('8080', $r->getUri()->getPort());
        $this->assertEquals('/path', $r->getUri()->getPath());
        $this->assertEquals('foo=bar&baz=qux', $r->getUri()->getQuery());

        // URLEncoded

        $r = $client->requestFactory('POST', 'http://whatever:8080/path', null, array('foo' => 'bar', 'baz' => 'qux'),
            array('content-type' => 'application/x-www-form-urlencoded'));

        $this->assertEquals('foo=bar&baz=qux', $r->getBody());

        // JSON

        $r = $client->requestFactory('POST', 'http://whatever', null, array('foo' => 'bar', 'baz' => 'qux'),
            array('content-type' => 'application/json'));

        $this->assertEquals('{"foo":"bar","baz":"qux"}', $r->getBody());

        // JSON by default

        $r = $client->requestFactory('POST', 'http://whatever', null, array('foo' => 'bar', 'baz' => 'qux'));

        $this->assertEquals('{"foo":"bar","baz":"qux"}', $r->getBody());

        // Foo content type

        $r = $client->requestFactory('POST', 'http://whatever', null, 'foo-encoded-text',
            array('content-type' => 'foo'));

        $this->assertEquals('foo-encoded-text', $r->getBody());

    }

}