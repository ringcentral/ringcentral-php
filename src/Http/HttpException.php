<?php

namespace RingCentral\SDK\Http;

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

        if ($error = $transaction->getError()) {
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