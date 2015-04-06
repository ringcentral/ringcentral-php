<?php

namespace RC\core;

use Pubnub\Pubnub;
use RC\http\Request;
use RC\http\RequestMock;
use RC\http\ResponseMockCollection;
use RC\subscription\PubnubMock;

class Context
{

    protected $_usePubnubMock = false;
    protected $_useRequestMock = false;

    /** @var ResponseMockCollection */
    protected $_responseMockCollection;

    public function __construct()
    {
        $this->_responseMockCollection = new ResponseMockCollection();
    }

    /**
     * @param array $options
     * @return Pubnub
     */
    public function getPubnub(array $options)
    {
        return $this->_usePubnubMock
            ? new PubnubMock($options)
            : new Pubnub($options);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $queryParams
     * @param mixed  $body
     * @param array  $headers
     * @return Request
     */
    public function getRequest($method = '', $url = '', $queryParams = array(), $body = null, $headers = array())
    {
        return $this->_useRequestMock
            ? new RequestMock($this->getResponseMockCollection(), $method, $url, $queryParams, $body, $headers)
            : new Request($method, $url, $queryParams, $body, $headers);
    }

    public function usePubnubStub($flag = false)
    {
        $this->_usePubnubMock = !!$flag;
        return $this;
    }

    public function useRequestStub($flag = false)
    {
        $this->_useRequestMock = !!$flag;
        return $this;
    }

    public function getResponseMockCollection()
    {
        return $this->_responseMockCollection;
    }

}
