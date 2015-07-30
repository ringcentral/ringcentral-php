<?php

namespace RingCentral\pubnub;

use Pubnub\Pubnub;

class PubnubFactory
{

    protected $useMock = false;

    public function setUseMock($flag = false)
    {
        $this->useMock = $flag;
        return $this;
    }

    /**
     * @param array $options
     * @return Pubnub|PubnubMock
     */
    public function getPubnub(array $options = array())
    {

        return ($this->useMock)
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
