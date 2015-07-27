<?php

namespace RingCentral\http\mocks;

use RingCentral\http\Request;
use RingCentral\http\Response;

class GenericMock extends Mock
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
    public function getResponse(Request $request)
    {

        return new Response($this->status, self::createBody($this->json));

    }

}