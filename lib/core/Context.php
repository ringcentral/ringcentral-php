<?php

namespace RingCentral\core;

use Pubnub\Pubnub;
use RingCentral\http\mocks\Mocks;
use RingCentral\http\Request;
use RingCentral\http\RequestMock;
use RingCentral\subscription\PubnubMock;

class Context
{

    protected $_usePubnubMock = false;
    protected $_useRequestMock = false;

    /** @var Mocks */
    protected $_mocks;

    public function __construct()
    {
        $this->_mocks = new Mocks();
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
     * @param string     $method
     * @param string     $url
     * @param array|null $queryParams
     * @param mixed      $body
     * @param array      $headers
     * @return Request
     */
    public function getRequest($method = '', $url = '', $queryParams = array(), $body = null, array $headers = array())
    {
        return $this->_useRequestMock
            ? new RequestMock($this->getMocks(), $method, $url, $queryParams, $body, $headers)
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

    public function getMocks()
    {
        return $this->_mocks;
    }

}
