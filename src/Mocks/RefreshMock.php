<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;

class RefreshMock extends AbstractMock
{

    protected $path = '/restapi/oauth/token';

    protected $failure = false;
    protected $expiresIn = 3600;

    public function __construct($failure = false, $expiresIn = 3600)
    {
        $this->failure = $failure;
        $this->expiresIn = $expiresIn;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(RequestInterface $request)
    {

        if (!$this->failure) {

            return self::createBody(array(
                'access_token'             => 'ACCESS_TOKEN_FROM_REFRESH',
                'token_type'               => 'bearer',
                'expires_in'               => $this->expiresIn,
                'refresh_token'            => 'REFRESH_TOKEN_FROM_REFRESH',
                'refresh_token_expires_in' => 60480,
                'scope'                    => 'SMS RCM Foo Boo',
                'expireTime'               => time() + 3600,
                'owner_id'                 => 'foo'
            ));

        } else {

            return self::createBody(array(
                'message' => 'Wrong token (mock)'
            ), 400);

        }

    }

    /**
     * @inheritdoc
     */
    public function test(RequestInterface $request)
    {

        return parent::test($request) && stristr($request->getBody(), 'grant_type=refresh_token');

    }

}