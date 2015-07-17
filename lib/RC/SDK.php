<?php

namespace RC;

use RC\client\AccountApi;
use RC\client\APIVersioningApi;
use RC\client\CallLogApi;
use RC\client\ClientApplicationInfoApi;
use RC\client\DictionaryApi;
use RC\client\MessagesApi;
use RC\client\NotificationsSubscriptionAPIApi;
use RC\client\PresenceApi;
use RC\client\RingOutApi;
use RC\core\Context;
use RC\platform\Platform;
use RC\subscription\Subscription;
use RC\client\ApiClient;
use RC\client\ExtensionApi;

class SDK
{

    const VERSION = '0.4.3';

    /** @var Platform */
    protected $platform;

    /** @var Context */
    protected $context;

    public function __construct($appKey, $appSecret, $server)
    {

        $this->context = new Context();

        $this->platform = new Platform($this->context, $appKey, $appSecret, $server);

        $this->client = new ApiClient($this->platform);

        $this->extension = new ExtensionApi($this->client);
        $this->account = new AccountApi($this->client);
        $this->versioning = new APIVersioningApi($this->client);
        $this->callLog = new CallLogApi($this->client);
        $this->clientInfo = new ClientApplicationInfoApi($this->client);
        $this->dictionary = new DictionaryApi($this->client);
        $this->messages = new MessagesApi($this->client);
        $this->notifications = new NotificationsSubscriptionAPIApi($this->client);
        $this->presence = new PresenceApi($this->client);
        $this->ringout = new RingOutApi($this->client);

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