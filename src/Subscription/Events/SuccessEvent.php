<?php

namespace RingCentral\SDK\Subscription\Events;

use RingCentral\SDK\Http\Transaction;

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