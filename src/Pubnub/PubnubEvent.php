<?php

namespace RingCentral\SDK\Pubnub;

use Symfony\Component\EventDispatcher\Event;

class PubnubEvent extends Event
{

    protected $data = array();

    public function __construct($data = array())
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

}