<?php

namespace RingCentral\http\mocks;

use RingCentral\http\Request;
use RingCentral\http\Response;

class RefreshMock extends Mock
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
    public function getResponse(Request $request)
    {

        if (!$this->failure) {

            return new Response(200, self::createBody(array(
                'access_token'             => 'ACCESS_TOKEN_FROM_REFRESH',
                'token_type'               => 'bearer',
                'expires_in'               => $this->expiresIn,
                'refresh_token'            => 'REFRESH_TOKEN_FROM_REFRESH',
                'refresh_token_expires_in' => 60480,
                'scope'                    => 'SMS RCM Foo Boo',
                'expireTime'               => time() + 3600,
                'owner_id'                 => 'foo'
            )));
        } else {

            return new Response(400, self::createBody(array(
                'message' => 'Wrong token (mock)'
            )));

        }

    }

    /**
     * @inheritdoc
     */
    public function test(Request $request)
    {

        $body = $request->getBody();

        return parent::test($request) &&
               !empty($body) &&
               !empty($body['grant_type']) &&
               $body['grant_type'] == 'refresh_token';

    }

}