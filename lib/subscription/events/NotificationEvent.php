<?php

namespace RingCentral\subscription\events;

class NotificationEvent
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