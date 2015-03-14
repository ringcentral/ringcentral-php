<?php

namespace RC\subscription;

use GuzzleHttp\Event\AbstractEvent;

class ErrorEvent extends AbstractEvent
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