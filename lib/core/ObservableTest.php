<?php

use RingCentral\core\Observable;

class ObservableTest extends PHPUnit_Framework_TestCase
{

    public function testOnAndEmit()
    {

        $o = new Observable();
        $o->on('foo', function () {
            return 'bar';
        });

        $this->assertEquals('bar', $o->emit('foo'));

    }

    public function testMultipleOnAndEmit()
    {

        $res = '';

        $o = new Observable();
        $o->on('foo', function () use (&$res) {
            $res .= '1';
        });
        $o->on('foo', function () use (&$res) {
            $res .= '2';
        });
        $o->on('foo', function () use (&$res) {
            $res .= '3';
        });

        $o->emit('foo');

        $this->assertEquals('123', $res);

    }

    public function testStopEmitOnFalse()
    {

        $res = false;

        $o = new Observable();
        $o->on('foo', function () use (&$res) {
            return false;
        });
        $o->on('foo', function () use (&$res) {
            $res = true;
            return $res;
        });

        $this->assertEquals(false, $o->emit('foo'));
        $this->assertEquals(false, $res);

    }

    public function testEmitReturnsLastReturn()
    {

        $res = false;
        $o = new Observable();
        $o->on('foo', function () use (&$res) {
            $res = true;
            return '1';
        });
        $o->on('foo', function () use (&$res) {
            return '2';
        });

        $this->assertEquals('2', $o->emit('foo'));
        $this->assertEquals(true, $res);

    }

    public function testOff()
    {

        $o = new Observable();
        $o->on('foo', function () use (&$res) {
            return '1';
        });
        $o->off('foo');

        $this->assertNotEquals('1', $o->emit('foo'));

    }

    public function testOffWithCallable()
    {

        $o = new Observable();

        $c = function () use (&$res) {
            return '1';
        };

        $o->on('foo', $c);
        $o->on('foo', function () use (&$res) {
            return '1';
        });
        $o->on('foo', $c);

        $this->assertEquals(3, $o->hasListeners('foo'));

        $o->off('foo', $c);

        $this->assertEquals(1, $o->hasListeners('foo'));

    }

}