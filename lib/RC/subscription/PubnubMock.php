<?php

namespace RC\subscription;

use Pubnub\Pubnub;
use RC\core\Observable;

class PubnubMock extends Pubnub
{

    /** @var callable */
    private $onMessage;

    /** @var Observable */
    private $observer;

    public function __construct(array $options)
    {

        $this->observer = new Observable();

    }

    public function subscribe($address, $cb = null)
    {

        $this->observer->on('message', $this->onMessage);

    }

    public function receiveMessage($message)
    {

        $this->observer->emit('message', $message);

    }

}