<?php

namespace RC\subscription;

use Exception;
use GuzzleHttp\Event\Emitter;
use phpseclib\Crypt\AES;
use Pubnub\Pubnub;
use RC\http\Response;
use RC\platform\Platform;

class Subscription extends Emitter
{

    const EVENT_NOTIFICATION = 'notification';
    const EVENT_REMOVE_SUCCESS = 'removeSuccess';
    const EVENT_REMOVE_ERROR = 'removeError';
    const EVENT_RENEW_SUCCESS = 'renewSuccess';
    const EVENT_RENEW_ERROR = 'renewError';
    const EVENT_SUBSCRIBE_SUCCESS = 'subscribeSuccess';
    const EVENT_SUBSCRIBE_ERROR = 'subscribeError';

    /** @var Platform */
    protected $platform;

    /** @var string[] */
    protected $eventFilters = [];

    protected $subscription = [
        'eventFilters'   => [],
        'expirationTime' => '', // 2014-03-12T19:54:35.613Z
        'expiresIn'      => 0,
        'deliveryMode'   => [
            'transportType' => 'PubNub',
            'encryption'    => false,
            'address'       => '',
            'subscriberKey' => '',
            'secretKey'     => ''
        ],
        'id'             => '',
        'creationTime'   => '', // 2014-03-12T19:54:35.613Z
        'status'         => '', // Active
        'uri'            => ''
    ];

    /** @var Pubnub */
    protected $pubnub;

    protected $keepPolling = false;

    public function __construct(Platform $platform)
    {

        $this->platform = $platform;

    }

    /**
     * @param array $options
     * @return Response
     * @throws Exception
     */
    public function register(array $options = [])
    {
        if ($this->isSubscribed()) {
            return $this->renew($options);
        } else {
            return $this->subscribe($options);
        }
    }

    public function setKeepPolling($flag)
    {
        $this->keepPolling = !empty($flag);
    }

    public function getKeepPolling()
    {
        return $this->keepPolling;
    }

    public function addEvents(array $events)
    {
        $this->eventFilters = array_merge($this->eventFilters, $events);
        return $this;
    }

    public function setEvents(array $events)
    {
        $this->eventFilters = $events;
        return $this;
    }

    public function subscribe(array $options = [])
    {

        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        try {

            $response = $this->platform->post('/restapi/v1.0/subscription', [
                'json' => [
                    'eventFilters' => $this->getFullEventFilters(),
                    'deliveryMode' => [
                        'transportType' => 'PubNub'
                    ]
                ]
            ]);

            $this->updateSubscription($response->json(['object' => false]));
            $this->subscribeAtPubnub();

            //TODO Subscription renewal when everything will become async

            $this->emit(self::EVENT_SUBSCRIBE_SUCCESS, new SuccessEvent($response));

            return $response;

        } catch (Exception $e) {

            $this->unsubscribe();
            $this->emit(self::EVENT_SUBSCRIBE_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    public function renew(array $options = [])
    {

        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        try {

            $response = $this->platform->put('/restapi/v1.0/subscription/' . $this->subscription['id'], [
                'json' => [
                    'eventFilters' => $this->getFullEventFilters()
                ]
            ]);

            $this->updateSubscription($response->json(['object' => false]));

            $this->emit(self::EVENT_RENEW_SUCCESS, new SuccessEvent($response));

            return $response;

        } catch (Exception $e) {

            $this->unsubscribe();
            $this->emit(self::EVENT_RENEW_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    public function remove(array $options = [])
    {

        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        try {

            $response = $this->platform->delete('/restapi/v1.0/subscription/' . $this->subscription['id']);

            $this->unsubscribe();

            $this->emit(self::EVENT_REMOVE_SUCCESS, new SuccessEvent($response));

            return $response;

        } catch (Exception $e) {

            $this->unsubscribe();
            $this->emit(self::EVENT_REMOVE_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    public function isSubscribed()
    {
        return (!empty($this->subscription) &&
                !empty($this->subscription['deliveryMode']) &&
                !empty($this->subscription['deliveryMode']['subscriberKey']) &&
                !empty($this->subscription['deliveryMode']['address']));
    }


    private function getFullEventFilters()
    {
        return array_map(function ($event) {
            return $this->platform->apiUrl($event);
        }, $this->eventFilters);
    }

    protected function updateSubscription($subscription)
    {
        $this->subscription = $subscription;
        return $this;
    }

    protected function unsubscribe()
    {

        if ($this->pubnub && $this->isSubscribed()) {
            //$this->pubnub->unsubscribe($this->subscription['deliveryMode']['address']);
            $this->pubnub = null;
        }

        $this->subscription = null;

    }

    protected function subscribeAtPubnub()
    {

        if (!$this->isSubscribed()) {
            return $this;
        }

        $this->pubnub = new Pubnub([
            'publish_key'   => 'foo',
            'subscribe_key' => $this->subscription['deliveryMode']['subscriberKey']
        ]);

        //print 'PUBNUB object created' . PHP_EOL;

        $this->pubnub->subscribe($this->subscription['deliveryMode']['address'], function ($message) {
            $this->notify($message['message']); // chanel, timeToken
            return $this->keepPolling;
        });

        //print 'PUBNUB subscription created' . PHP_EOL;

        return $this;

    }

    protected function notify($message)
    {

        if ($this->isSubscribed() && $this->subscription['deliveryMode']['encryptionKey']) {

            $cipher = new AES(AES::MODE_ECB);
            $cipher->setKey(base64_decode($this->subscription['deliveryMode']['encryptionKey']));
            $message = $cipher->decrypt(base64_decode($message));

        }

        //print 'Message received: ' . $message . PHP_EOL;

        $message = json_decode($message);

        $this->emit(self::EVENT_NOTIFICATION, new NotificationEvent($message));

        return $this;

    }

}