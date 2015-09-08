<?php

namespace RingCentral\SDK\Http;

use Exception;
use Psr\Http\Message\RequestInterface;
use RingCentral\SDK\Mocks\Registry;

class ClientMock extends Client
{

    /** @var Registry */
    protected $mockRegistry;

    public function __construct(Registry $registry)
    {
        $this->mockRegistry = $registry;
    }

    /**
     * @param RequestInterface $request
     * @return ApiResponse
     * @throws Exception
     */
    protected function loadResponse(RequestInterface $request)
    {

        $responseMock = $this->mockRegistry->find($request);

        if (empty($responseMock)) {
            throw new Exception(sprintf('Mock for "%s" has not been found in registry', $request->getUri()));
        }

        $responseBody = $responseMock->getResponse($request);

        return new ApiResponse($request, $responseBody);

    }

}