<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;

abstract class AbstractMock
{

    protected $path = '';

    /**
     * Factory method that creates Response object based on given Request object
     * @param RequestInterface $request
     * @return string
     */
    public abstract function getResponse(RequestInterface $request);

    /**
     * Method verifies that mock is applicable for given Request
     * @param RequestInterface $request
     * @return boolean
     */
    public function test(RequestInterface $request)
    {
        return (stristr($request->getUri()->getPath(), $this->path));
    }

    /**
     * Helper function to generate response headers + body as text
     * @param array $body
     * @param int   $status
     * @param array $headers
     * @return string
     */
    protected static function createBody(
        array $body = array(),
        $status = 200,
        array $headers = array('content-type' => 'application/json')
    ) {

        $res = array('HTTP/1.1 ' . $status . ' ReasonPhrase Not Implemented In Mocks');

        foreach ($headers as $k => $v) {
            $res[] = $k . ': ' . $v;
        }

        $res[] = '';

        $res[] = json_encode($body);

        return join("\n", $res);

    }

}