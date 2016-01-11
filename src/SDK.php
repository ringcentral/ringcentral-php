<?php

namespace RingCentral\SDK;

use RingCentral\SDK\Http\Client as HttpClient;
use RingCentral\SDK\Http\MultipartBuilder;
use RingCentral\SDK\Mocks\Client as MockClient;
use RingCentral\SDK\Mocks\Registry;
use RingCentral\SDK\Platform\Platform;
use RingCentral\SDK\Pubnub\PubnubFactory;
use RingCentral\SDK\Subscription\Subscription;

class SDK
{

    const VERSION = '1.1.1';
    const SERVER_PRODUCTION = 'https://platform.ringcentral.com';
    const SERVER_SANDBOX = 'https://platform.devtest.ringcentral.com';

    /** @var Registry */
    protected $_mockRegistry;

    /** @var Platform */
    protected $_platform;

    /** @var PubnubFactory */
    protected $_pubnubFactory;

    /** @var HttpClient */
    protected $_client;

    public function __construct(
        $appKey,
        $appSecret,
        $server,
        $appName = '',
        $appVersion = '',
        $useHttpMock = false,
        $usePubnubMock = false
    ) {

        $pattern = "/[^a-z0-9-_.]/i";

        $appName = preg_replace($pattern, '', $appName);
        $appVersion = preg_replace($pattern, '', $appVersion);

        $this->_mockRegistry = new Registry();

        $this->_pubnubFactory = new PubnubFactory($usePubnubMock);

        $this->_client = $useHttpMock
            ? new MockClient($this->_mockRegistry)
            : new HttpClient();

        $this->_platform = new Platform($this->_client, $appKey, $appSecret, $server, $appName, $appVersion);

    }

    public function mockRegistry()
    {
        return $this->_mockRegistry;
    }

    public function platform()
    {
        return $this->_platform;
    }

    public function createSubscription()
    {
        return new Subscription($this->_pubnubFactory, $this->_platform);
    }

    public function createMultipartBuilder()
    {
        return new MultipartBuilder();
    }

}