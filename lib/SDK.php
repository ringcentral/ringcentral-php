<?php

namespace RingCentral;

use RingCentral\core\Context;
use RingCentral\platform\Platform;
use RingCentral\subscription\Subscription;

class SDK
{

    const VERSION = '0.5.0';

    /** @var Platform */
    protected $platform;

    /** @var Context */
    protected $context;

    public function __construct($appKey, $appSecret, $server)
    {

        $this->context = new Context();

        $this->platform = new Platform($this->context, $appKey, $appSecret, $server);

    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function getSubscription()
    {
        return new Subscription($this->context, $this->platform);
    }

    public function getContext()
    {
        return $this->context;
    }

}