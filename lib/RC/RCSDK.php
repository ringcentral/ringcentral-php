<?php

namespace RC;

use RC\core\Cache;

class RCSDK
{

    /** @var core\Platform */
    protected $platform = null;

    public function __construct(Cache $cache, $appKey, $appSecret, $server = '')
    {

        $this->platform = new core\Platform($cache, $appKey, $appSecret, $server);

    }

    public function getPlatform()
    {
        return $this->platform;
    }

}