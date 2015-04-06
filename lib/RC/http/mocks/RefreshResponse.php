<?php

namespace RC\http\mocks;

use RC\http\Request;
use RC\http\Response;
use RC\http\ResponseMock;

class RefreshResponse extends ResponseMock
{

    protected $path = '/restapi/oauth/token';

    protected $failure = false;

    public function __construct($failure = false)
    {
        $this->failure = $failure;
    }

    public function getResponse(Request $request)
    {

        if (!$this->failure) {
            
            return new Response(200, self::createBody(array(
                'access_token'             => 'ACCESS_TOKEN',
                'token_type'               => 'bearer',
                'expires_in'               => 3600,
                'refresh_token'            => 'REFRESH_TOKEN',
                'refresh_token_expires_in' => 60480,
                'scope'                    => 'SMS RCM Foo Boo',
                'expireTime'               => time() + 3600
            )));
        } else {

            return new Response(400, self::createBody(array(
                'message' => 'Wrong token (mock)'
            )));

        }

    }

    public function test(Request $request)
    {

        return !$request->getBody() || !$request->getBody()['refresh_token'];

    }

}