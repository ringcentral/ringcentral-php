<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;

class GenericMock extends AbstractMock
{

    protected $status = 200;
    protected $json = array();

    public function __construct($path = '', array $json = array(), $status = 200)
    {
        $this->path = '/restapi/v1.0' . $path;
        $this->json = $json;
        $this->status = $status;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(RequestInterface $request)
    {

        return self::createBody($this->json, $this->status);

    }

}