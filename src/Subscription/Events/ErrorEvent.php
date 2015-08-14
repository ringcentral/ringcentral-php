<?php

namespace RingCentral\SDK\Subscription\Events;

use Symfony\Component\EventDispatcher\Event;

class ErrorEvent extends Event
{

    protected $exception;

    public function __construct(\Exception $exception)
    {

        $this->exception = $exception;

    }

    public function getException()
    {
        return $this->exception;
    }

}