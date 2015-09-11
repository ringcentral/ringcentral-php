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
    public function pubnub(array $options = array())
    {

        if ($this->_useMock) {
            return new PubnubMock($options);
        }

        return new Pubnub($options);

    }

}
