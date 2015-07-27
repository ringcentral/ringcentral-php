<?php

namespace RingCentral\subscription;

use Pubnub\Pubnub;
use RingCentral\core\Observable;

class PubnubMock extends Pubnub
{

    /** @var Observable */
    private $observer;

    public function __construct(array $options)
    {

        $this->observer = new Observable();

    }

    public function subscribe($channel, $cb, $timeToken = 0, $presence = false)
    {

        $this->observer->on('message', $cb);

    }

    public function receiveMessage($message)
    {

        $this->observer->emit('message', array(
            'message' => $message,
            'channel' => null,
            'timeToken' => time()
        ));

    }

}