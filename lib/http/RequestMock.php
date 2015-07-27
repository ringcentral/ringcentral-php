<?php

namespace RingCentral\http;

use Exception;
use RingCentral\http\mocks\Mocks;

class RequestMock extends Request
{

    /** @var Mocks */
    protected $mocks;

    /**
     * @param Mocks        $mocks
     * @param string       $method
     * @param string       $url
     * @param array|null   $queryParams
     * @param array|string $body
     * @param array        $headers
     * @throws Exception
     */
    public function __construct(
        Mocks $mocks,
        $method,
        $url,
        $queryParams = array(),
        $body = null,
        array $headers = array()
    ) {
        parent::__construct($method, $url, $queryParams, $body, $headers);
        $this->mocks = $mocks;
    }

    /**
     * @return $this
     * @throws HttpException
     */
    public function send()
    {

        $responseMock = $this->mocks->find($this);

        if (empty($responseMock)) {
            throw new HttpException($this, null, new Exception('Mock was not found in contextual mocks registry'));
        }

        $response = $responseMock->getResponse($this);

        if (!$response->checkStatus()) {
            throw new HttpException($this, $response, new Exception('Response has unsuccessful status'));
        }

        return $response;

    }

}