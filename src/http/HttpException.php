<?php

namespace RingCentral\http;

use Exception;

class HttpException extends Exception
{

    /** @var Transaction */
    private $transaction;

    public function __construct(
        Transaction $transaction,
        Exception $previous = null
    ) {

        $this->transaction = $transaction;

        $message = $previous ? $previous->getMessage() : 'Unknown error';
        $status = $previous ? $previous->getCode() : 0;

        $error = $transaction->getError();

        if ($error) {
            $message = $error;
        }

        if ($transaction->getResponse() && $statusCode = $transaction->getResponse()->getStatusCode()) {
            $status = $statusCode;
        }

        parent::__construct($message, $status, $previous);

    }

    public function getTransaction()
    {
        return $this->transaction;
    }

}