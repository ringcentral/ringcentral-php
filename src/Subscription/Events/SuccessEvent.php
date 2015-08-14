<?php

namespace RingCentral\SDK\Subscription\Events;

use RingCentral\SDK\Http\Transaction;
use Symfony\Component\EventDispatcher\Event;

class SuccessEvent extends Event
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