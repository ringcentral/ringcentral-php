<?php

namespace RC\http;

use Exception;

class RequestMock extends Request
{

    /** @var ResponseMockCollection */
    protected $collection;

    public function __construct(
        ResponseMockCollection $collection,
        $method = '',
        $url = '',
        $queryParams = array(),
        $body = null,
        $headers = array()
    ) {
        parent::__construct($method, $url, $queryParams, $body, $headers);
        $this->collection = $collection;
    }

    /**
     * @return $this
     * @throws HttpException
     */
    public function send()
    {

        $responseMock = $this->collection->find($this);

        if (empty($responseMock)) {
            throw new HttpException($this, null, new Exception('Response was not found in collection'));
        }

        $response = $responseMock->getResponse();

        if (!$response->isSuccess()) {
            throw new HttpException($this, $response, new Exception('Response has unsuccessful status'));
        }

        return $response;

    }

}