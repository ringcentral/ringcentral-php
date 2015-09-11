<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;

class Mock
{

    protected $_path = '';
    protected $_method = 'GET';
    protected $_status = 200;
    protected $_json = array();

    public function __construct($method = 'GET', $path = '', array $json = array(), $status = 200)
    {
        $this->_method = $method;
        $this->_path = $path; //'/restapi/v1.0' .
        $this->_json = $json;
        $this->_status = $status;
    }

    /**
     * Factory method that creates Response object based on given Request object
     * @param RequestInterface $request
     * @return string
     */
    public function response(RequestInterface $request)
    {
        return self::createBody($this->_json, $this->_status);
    }

    public function path()
    {
        return $this->_path;
    }

    public function method()
    {
        return $this->_method;
    }

    /**
     * Method verifies that mock is applicable for given Request
     * @param RequestInterface $request
     * @return boolean
     */
    public function test(RequestInterface $request)
    {
        return (stristr($request->getUri()->getPath(), $this->_path) &&
                strtoupper($request->getMethod()) == $this->_method
        );
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