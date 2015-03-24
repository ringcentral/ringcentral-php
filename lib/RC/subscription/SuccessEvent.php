<?php

namespace RC\subscription;

use GuzzleHttp\Event\AbstractEvent;
use RC\http\Response;

class SuccessEvent extends AbstractEvent
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