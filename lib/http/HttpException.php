<?php

namespace RingCentral\http;

use Exception;

class HttpException extends Exception
{

    /** @var Request */
    protected $request = null;

    /** @var Response */
    protected $response = null;

    public function __construct(Request $request = null, Response $response = null, Exception $previous = null)
    {

        $message = $previous ? $previous->getMessage() : 'Unknown error';
        $status = 0;

        $this->request = $request;
        $this->response = $response;

        if ($response) {

            $message = $response->getError();
            $status = $response->getStatus();

            //TODO Add status text

        }

        parent::__construct($message, $status, $previous);

    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

}