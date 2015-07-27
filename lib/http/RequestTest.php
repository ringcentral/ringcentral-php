<?php

use RingCentral\http\Request;
use RingCentral\test\TestCase;

class RequestTest extends TestCase
{

    public function testGetUrlWithQueryString()
    {

        $r = new Request('GET', 'http://whatever', array('foo' => 'bar', 'baz' => 'qux'));

        $this->assertEquals('http://whatever?foo=bar&baz=qux', $r->getUrlWithQueryString());

    }

    public function testGetEncodedBody()
    {

        $r1 = new Request('POST', 'http://whatever', null, array('foo' => 'bar', 'baz' => 'qux'), array('content-type' => 'application/x-www-form-urlencoded'));
        $r2 = new Request('POST', 'http://whatever', null, array('foo' => 'bar', 'baz' => 'qux'), array('content-type' => 'application/json'));
        $r3 = new Request('POST', 'http://whatever', null, array('foo' => 'bar', 'baz' => 'qux'));
        $r4 = new Request('POST', 'http://whatever', null, 'foo-encoded-text', array('content-type' => 'foo'));

        $this->assertEquals('foo=bar&baz=qux', $r1->getEncodedBody());
        $this->assertEquals('{"foo":"bar","baz":"qux"}', $r2->getEncodedBody());
        $this->assertEquals('{"foo":"bar","baz":"qux"}', $r3->getEncodedBody()); // JSON by default
        $this->assertEquals('foo-encoded-text', $r4->getEncodedBody());

    }

    public function testIsMethods()
    {

        $r1 = new Request('GET', 'http://whatever');
        $r2 = new Request('POST', 'http://whatever');
        $r3 = new Request('PUT', 'http://whatever');
        $r4 = new Request('DELETE', 'http://whatever');

        $this->assertTrue($r1->isGet() && !$r1->isPost() && !$r1->isPut() && !$r1->isDelete());
        $this->assertTrue(!$r2->isGet() && $r2->isPost() && !$r2->isPut() && !$r2->isDelete());
        $this->assertTrue(!$r3->isGet() && !$r3->isPost() && $r3->isPut() && !$r3->isDelete());
        $this->assertTrue(!$r4->isGet() && !$r4->isPost() && !$r4->isPut() && $r4->isDelete());

    }

    public function testGetSetBody()
    {

        $r = new Request('GET', 'http://whatever');

        $this->assertEquals('foo', $r->setBody('foo')->getBody());

    }

    public function testGetSetQueryParams()
    {

        $r = new Request('GET', 'http://whatever');

        $this->assertEquals(array('foo' => 'bar'), $r->setQueryParams(array('foo' => 'bar'))->getQueryParams());

    }

    public function testGetSetMethod()
    {

        $r = new Request('GET', 'http://whatever');

        $this->assertEquals('POST', $r->setMethod('POST')->getMethod());

    }

}