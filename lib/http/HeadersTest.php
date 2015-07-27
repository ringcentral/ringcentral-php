<?php

use RingCentral\http\Headers;
use RingCentral\test\TestCase;

class HeadersTest extends TestCase
{

    public function testIgnoresCase()
    {

        $h = new Headers();

        $this->assertEquals('foo', $h
            ->setHeader('CoNtEnT-tYpE', 'foo')
            ->getHeader('cOnTeNt-TyPe'));

    }

    public function testGetContentType()
    {

        $h = new Headers();

        $this->assertEquals('foo', $h->setHeader('CoNtEnT-tYpE', 'foo')->getContentType());

    }

    public function testSetHeadersAndGetHeadersArray()
    {

        $h = new Headers();

        $h->setHeaders(array(
            'foo' => 'foo',
            'FOO' => 'FOO', // this header overwrites the previous
            'bar' => 'bar'
        ));

        $this->assertEquals(array('foo:FOO', 'bar:bar'), $h->getHeadersArray());

    }

    public function testIsContentType()
    {

        $h = new Headers();

        $this->assertTrue($h->setHeader('Content-Type', 'fooBARfoo')->isContentType('bar'));
        $this->assertTrue($h->setHeader('Content-Type', 'fooBARfoo')->isContentType('FOO'));
        $this->assertFalse($h->setHeader('Content-Type', 'fooBARfoo')->isContentType('BAZ'));

    }

    public function testSpecialContentTypes()
    {

        $h = new Headers();

        $this->assertTrue($h->setHeader('Content-Type', 'application/json; boundary=foo')->isJson());
        $this->assertTrue($h->setHeader('Content-Type', 'multipart/mixed; boundary=foo')->isMultipart());
        $this->assertTrue($h->setHeader('Content-Type', 'application/x-www-form-urlencoded; boundary=foo')->isUrlEncoded());

    }

}