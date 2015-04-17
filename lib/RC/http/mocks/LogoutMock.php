<?php

namespace RC\http\mocks;

use RC\http\Request;
use RC\http\Response;

class LogoutMock extends Mock
{

    protected $path = '/restapi/oauth/revoke';

    /**
     * @inheritdoc
     */
    public function getResponse(Request $request)
    {

        return new Response(200, self::createBody());

    }

}