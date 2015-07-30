<?php

namespace RingCentral\mocks;

use Psr\Http\Message\RequestInterface;
use RingCentral\http\Transaction;

class LogoutMock extends AbstractMock
{

    protected $path = '/restapi/oauth/revoke';

    /**
     * @inheritdoc
     */
    public function getResponse(RequestInterface $request)
    {

        return self::createBody();

    }

}