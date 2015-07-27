<?php

namespace RingCentral\http\mocks;

use RingCentral\http\Request;
use RingCentral\http\Response;

class AuthenticationMock extends Mock
{

    protected $path = '/restapi/oauth/token';

    /**
     * @inheritdoc
     */
    public function getResponse(Request $request)
    {

        return new Response(200, self::createBody(array(
            'access_token'             => 'ACCESS_TOKEN',
            'token_type'               => 'bearer',
            'expires_in'               => 3600,
            'refresh_token'            => 'REFRESH_TOKEN',
            'refresh_token_expires_in' => 60480,
            'scope'                    => 'SMS RCM Foo Boo',
            'expireTime'               => time() + 3600,
            'owner_id'                 => 'foo'
        )));

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
               $body['grant_type'] == 'password';

    }

}