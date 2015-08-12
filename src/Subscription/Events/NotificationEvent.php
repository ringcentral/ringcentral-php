<?php

namespace RingCentral\SDK\Subscription\Events;

use Symfony\Component\EventDispatcher\Event;

class NotificationEvent extends Event
{

    protected $payload = array();

    public function __construct(array $payload)
    {

        $this->payload = $payload;

    }

    public function getPayload()
    {
        return $this->payload;
    }

}