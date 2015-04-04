<?php

namespace RC;

use RC\platform\Platform;
use RC\subscription\Subscription;

class SDK
{

    const VERSION = '0.4.0';

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

    public function getSubscription()
    {
        return new Subscription($this->platform);
    }

}