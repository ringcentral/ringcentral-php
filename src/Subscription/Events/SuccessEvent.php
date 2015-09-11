<?php

namespace RingCentral\SDK\Subscription\Events;

use RingCentral\SDK\Http\ApiResponse;
use Symfony\Component\EventDispatcher\Event;

class SuccessEvent extends Event
{

    /** @var ApiResponse */
    protected $_response;

    public function __construct(ApiResponse $response)
    {

        $this->_response = $response;

    }

    public function apiResponse()
    {
        return $this->_response;
    }

}