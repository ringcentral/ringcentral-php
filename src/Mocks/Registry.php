<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

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

        /** @var AbstractMock $mock */
        $mock = array_shift($this->responses);

        if (empty($mock)) {
            throw new Exception('No mock in registry for request ' .
                                $request->getMethod() . ' ' . $request->getUri());
        }

        if (!$mock->test($request)) {
            throw new Exception('Wrong request ' . $request->getMethod() . ' ' . $request->getUri() .
                                ' for expected mock ' . $mock->method() . ' ' . $mock->path());
        }

        return $mock;

    }

    public function clear()
    {
        $this->responses = array();
        return $this;
    }

}