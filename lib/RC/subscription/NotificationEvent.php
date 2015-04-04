<?php

namespace RC\subscription;

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