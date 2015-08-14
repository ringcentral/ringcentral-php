<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;

class AuthenticationMock extends AbstractMock
{

    protected $path = '/restapi/oauth/token';

    /**
     * @inheritdoc
     */
    public function getResponse(RequestInterface $request)
    {

        return self::createBody(array(
            'access_token'             => 'ACCESS_TOKEN',
            'token_type'               => 'bearer',
            'expires_in'               => 3600,
            'refresh_token'            => 'REFRESH_TOKEN',
            'refresh_token_expires_in' => 60480,
            'scope'                    => 'SMS RCM Foo Boo',
            'expireTime'               => time() + 3600,
            'owner_id'                 => 'foo'
        ));

    }

    /**
     * @inheritdoc
     */
    public function test(RequestInterface $request)
    {

        return parent::test($request) && stristr($request->getBody(), 'grant_type=password');

    }

}