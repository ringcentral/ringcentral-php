<?php

namespace RC\subscription;

class ErrorEvent
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