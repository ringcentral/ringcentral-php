<?php

namespace RingCentral\http\mocks;

use RingCentral\http\Request;
use RingCentral\http\Response;

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