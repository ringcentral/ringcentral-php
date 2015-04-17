<?php

namespace RC\http\mocks;

use RC\http\Request;
use RC\http\Response;

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