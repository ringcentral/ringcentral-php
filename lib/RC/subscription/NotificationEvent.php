<?php

namespace RC\subscription;

use GuzzleHttp\Event\AbstractEvent;
use stdClass;

class NotificationEvent extends AbstractEvent
{

    /** @var stdClass */
    protected $payload = [];

    public function __construct(stdClass $payload)
    {

        $this->payload = $payload;

    }

    public function getPayload()
    {
        return $this->payload;
    }

}