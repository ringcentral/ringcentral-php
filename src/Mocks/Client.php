<?php

namespace RingCentral\SDK\Mocks;

use Exception;
use Psr\Http\Message\RequestInterface;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\Http\Client as HttpClient;

class Client extends HttpClient
{

    /** @var Registry */
    protected $_registry;

    public function __construct(Registry $registry)
    {
        $this->_registry = $registry;
    }

    /**
     * @param RequestInterface $request
     * @return ApiResponse
     * @throws Exception
     */
    protected function loadResponse(RequestInterface $request)
    {
        $mock = $this->_registry->find($request);
        return new ApiResponse($request, $mock->response($request));
    }

}