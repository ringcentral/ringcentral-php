<?php

namespace RC\http;

class ResponseMock
{

    protected $path = '';

    public function match(Request $request)
    {
        return (stristr($request->getUrl(), $this->path)) && $this->test($request);
    }

    public function getResponse()
    {
        return new Response(200, '{}');
    }

    protected function test(Request $request)
    {
        return !empty($request); // always true
    }

    protected static function createBody(array $body, array $headers = array('content-type' => 'application/json'))
    {

        $res = [];

        foreach ($headers as $k => $v) {
            $res[] = $k . ': ' . $v;
        }

        $res[] = '';

        $res[] = json_encode($body);

        return join("\n", $res);

    }

}