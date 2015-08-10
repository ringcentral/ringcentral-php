<?php

namespace RingCentral\SDK\Pubnub;

use Pubnub\Pubnub;
use RingCentral\SDK\core\Observable;

class PubnubMock extends Pubnub
{

    /** @var Observable */
    private $observer;

    public function __construct(array $options = array())
    {

        parent::__construct($options);
        $this->observer = new Observable();

    }

    public function subscribe($channel, $cb, $timeToken = 0, $presence = false)
    {

        $this->observer->on('message', $cb);

    }

    public function receiveMessage($message)
    {

        $this->observer->emit('message', array(
            'message'   => $message,
            'channel'   => null,
            'timeToken' => time()
        ));

    }

}