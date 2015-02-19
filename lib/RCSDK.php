<?php

namespace RC;

use RC\core\Cache;

class RCSDK
{

    /** @var core\Platform */
    protected $platform = null;

    public function __construct(Cache $cache)
    {

        $this->platform = new core\Platform($cache);

    }

    public function getPlatform()
    {
        return $this->platform;
    }

}