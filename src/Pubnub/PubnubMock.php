<?php

namespace RingCentral\SDK\Pubnub;

use Pubnub\Pubnub;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PubnubMock extends Pubnub
{

    /** @var EventDispatcher */
    private $eventDispatcher;

    public function __construct(array $options = array())
    {

        parent::__construct($options);
        $this->observer = new EventDispatcher();

    }

    public function subscribe($channel, $cb, $timeToken = 0, $presence = false)
    {

        $this->observer->addListener('message', function (PubnubEvent $e) use ($cb) {
            call_user_func($cb, $e->getData());
        });

    }

    public function receiveMessage($message)
    {

        $this->observer->dispatch('message', new PubnubEvent(array(
            'message'   => $message,
            'channel'   => null,
            'timeToken' => time()
        )));

    }

}