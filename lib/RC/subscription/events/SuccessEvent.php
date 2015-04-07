<?php

namespace RC\subscription\events;

use RC\http\Response;

class SuccessEvent
{

    /** @var Response */
    protected $response;

    public function __construct(Response $response)
    {

        $this->response = $response;

    }

    public function getResponse()
    {
        return $this->response;
    }

}