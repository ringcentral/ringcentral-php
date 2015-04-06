<?php

namespace RC\http\mocks;

use RC\http\Request;
use RC\http\Response;
use RC\http\ResponseMock;

class AuthenticationResponse extends ResponseMock
{

    protected $path = '/restapi/oauth/token';

    public function getResponse(Request $request)
    {

        return new Response(200, self::createBody(array(
            'access_token'             => 'ACCESS_TOKEN',
            'token_type'               => 'bearer',
            'expires_in'               => 3600,
            'refresh_token'            => 'REFRESH_TOKEN',
            'refresh_token_expires_in' => 60480,
            'scope'                    => 'SMS RCM Foo Boo',
            'expireTime'               => time() + 3600
        )));

    }

    public function test(Request $request)
    {

        return empty($request->getBody()) ||
               empty($request->getBody()['refresh_token']);

    }

}