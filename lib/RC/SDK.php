<?php

namespace RC;

use RC\platform\Parser;
use RC\platform\Platform;

class SDK
{

    const VERSION = '0.2.1';

    /** @var Platform */
    protected $platform;

    /** @var Parser */
    protected $parser;

    public function __construct($appKey, $appSecret, $server)
    {

        $this->parser = new Parser();

        $this->platform = new Platform($appKey, $appSecret, $server);

    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function getParser()
    {
        return $this->parser;
    }

}