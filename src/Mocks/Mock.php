<?php

namespace RingCentral\SDK\Mocks;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * @return ResponseInterface
     */
    public function response(RequestInterface $request)
    {
        return new Response($this->_status, array('content-type' => 'application/json'), json_encode($this->_json));
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

}