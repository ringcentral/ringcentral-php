<?php

namespace RingCentral\subscription\events;

use RingCentral\http\Transaction;

class SuccessEvent
{

    /** @var Transaction */
    protected $response;

    public function __construct(Transaction $response)
    {

        $this->transaction = $response;

    }

    public function getTransaction()
    {
        return $this->transaction;
    }

}