<?php

namespace RingCentral\SDK\Pubnub;

use Pubnub\Pubnub;

class PubnubFactory
{

    protected $_useMock = false;

    public function __construct($useMock = false)
    {
        $this->_useMock = $useMock;
    }

    /**
     * @param array $options
     * @return Pubnub|PubnubMock
     */
    public function getPubnub(array $options = array())
    {

        return ($this->_useMock)
            ? $this->getPubnubMock($options)
            : $this->getPubnubReal($options);

    }

    /**
     * @param array $options
     * @return Pubnub
     */
    public function getPubnubReal(array $options = array())
    {
        return new Pubnub($options);
    }

    /**
     * @param array $options
     * @return Pubnub
     */
    public function getPubnubMock(array $options = array())
    {
        return new PubnubMock($options);
    }

}
