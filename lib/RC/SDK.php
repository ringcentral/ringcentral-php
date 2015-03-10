<?php

namespace RC;

use RC\platform\Platform;

class SDK
{

    const VERSION = '0.3.0';

    /** @var Platform */
    protected $platform;

    public function __construct($appKey, $appSecret, $server)
    {

        $this->platform = new Platform($appKey, $appSecret, $server);

    }

    public function getPlatform()
    {
        return $this->platform;
    }

}