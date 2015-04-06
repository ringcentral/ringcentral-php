<?php

namespace RC\http;

class ResponseMockCollection
{

    /** @var ResponseMock[] */
    protected $responses = array();

    public function add(ResponseMock $requestMockResponse)
    {

        $this->responses[] = $requestMockResponse;
        return $this;

    }

    /**
     * @param Request $request
     * @return ResponseMock
     */
    public function find(Request $request)
    {

        $response = null;

        foreach ($this->responses as $res) {

            if ($res->match($request)) {
                $response = $res;
            }

        }

        return $response;

    }

    public function clear()
    {
        $this->responses = [];
    }

}