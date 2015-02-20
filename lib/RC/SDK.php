<?php

namespace RC;

use RC\cache\Cache;
use RC\platform\Platform;

class SDK
{

    /** @var Platform */
    protected $platform = null;

    public function __construct(Cache $cache, $appKey, $appSecret, $server = '')
    {

        $this->platform = new Platform($cache, $appKey, $appSecret, $server);

    }

    public function getPlatform()
    {
        return $this->platform;
    }

}