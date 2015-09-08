<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;

class LogoutMock extends AbstractMock
{

    protected $_path = '/restapi/oauth/revoke';
    protected $_method = 'POST';

    /**
     * @inheritdoc
     */
    public function getResponse(RequestInterface $request)
    {

        return self::createBody();

    }

}