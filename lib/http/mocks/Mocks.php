<?php

namespace RingCentral\http\mocks;

use RingCentral\http\Request;

class Mocks
{

    /** @var Mock[] */
    protected $responses = array();

    public function add(Mock $requestMockResponse)
    {

        $this->responses[] = $requestMockResponse;
        return $this;

    }

    /**
     * @param Request $request
     * @return Mock
     */
    public function find(Request $request)
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
    }

}