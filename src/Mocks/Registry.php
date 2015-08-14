<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;

class Registry
{

    /** @var AbstractMock[] */
    protected $responses = array();

    public function add(AbstractMock $requestMockResponse)
    {

        $this->responses[] = $requestMockResponse;
        return $this;

    }

    /**
     * @param RequestInterface $request
     * @return AbstractMock
     */
    public function find(RequestInterface $request)
    {

        $response = null;

        foreach ($this->responses as $res) {

            if ($res->test($request)) {
                $response = $res;
            }

        }

        return $response;

    }

    public function clear()
    {
        $this->responses = array();
        return $this;
    }

}