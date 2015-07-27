<?php

namespace RingCentral\http\mocks;

use RingCentral\http\Request;
use RingCentral\http\Response;

abstract class Mock
{

    protected $path = '';

    /**
     * Factory method that creates Response object based on given Request object
     * @param Request $request
     * @return Response
     */
    public function getResponse(Request $request)
    {
        return new Response(200, '{}');
    }

    /**
     * Method verifies that mock is applicable for given Request
     * @param Request $request
     * @return string
     */
    public function test(Request $request)
    {
        return (stristr($request->getUrl(), $this->path));
    }

    /**
     * Helper function to generate response headers + body as text
     * @param array $body
     * @param array $headers
     * @return string
     */
    protected static function createBody(array $body = array(), array $headers = array('content-type' => 'application/json'))
    {

        $res = array();

        foreach ($headers as $k => $v) {
            $res[] = $k . ': ' . $v;
        }

        $res[] = '';

        $res[] = json_encode($body);

        return join("\n", $res);

    }

}