<?php

namespace RingCentral\subscription;

use Crypt_AES;
use Exception;
use Pubnub\Pubnub;
use RingCentral\core\Context;
use RingCentral\core\Observable;
use RingCentral\http\Response;
use RingCentral\platform\Platform;
use RingCentral\subscription\events\ErrorEvent;
use RingCentral\subscription\events\NotificationEvent;
use RingCentral\subscription\events\SuccessEvent;

class Subscription extends Observable
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
    protected $eventFilters = array();

    protected $subscription = array(
        'eventFilters'   => array(),
        'expirationTime' => '', // 2014-03-12T19:54:35.613Z
        'expiresIn'      => 0,
        'deliveryMode'   => array(
            'transportType' => 'PubNub',
            'encryption'    => false,
            'address'       => '',
            'subscriberKey' => '',
            'secretKey'     => ''
        ),
        'id'             => '',
        'creationTime'   => '', // 2014-03-12T19:54:35.613Z
        'status'         => '', // Active
        'uri'            => ''
    );

    /** @var Pubnub */
    protected $pubnub;

    /** @var Context */
    protected $context;

    protected $keepPolling = false;

    public function __construct(Context $context, Platform $platform)
    {

        $this->platform = $platform;
        $this->context = $context;

    }

    /**
     * @param array $options
     * @return Response
     * @throws Exception
     */
    public function register(array $options = array())
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

    public function subscribe(array $options = array())
    {

        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        try {

            $response = $this->platform->post('/restapi/v1.0/subscription', null, array(
                'eventFilters' => $this->getFullEventFilters(),
                'deliveryMode' => array(
                    'transportType' => 'PubNub'
                )
            ));

            $this->updateSubscription($response->getJson(false));
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

    public function renew(array $options = array())
    {

        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        try {

            $response = $this->platform->put('/restapi/v1.0/subscription/' . $this->subscription['id'], null, array(
                'eventFilters' => $this->getFullEventFilters()
            ));

            $this->updateSubscription($response->getJson(false));

            $this->emit(self::EVENT_RENEW_SUCCESS, new SuccessEvent($response));

            return $this;

        } catch (Exception $e) {

            $this->unsubscribe();
            $this->emit(self::EVENT_RENEW_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    public function remove()
    {

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
        $events = array();
        foreach ($this->eventFilters as $event) {
            $events[] = $this->platform->apiUrl($event);
        }
        return $events;
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

        $this->pubnub = $this->context->getPubnub(array(
            'publish_key'   => 'foo',
            'subscribe_key' => $this->subscription['deliveryMode']['subscriberKey']
        ));

        //print 'PUBNUB object created' . PHP_EOL;

        $this->pubnub->subscribe($this->subscription['deliveryMode']['address'], array($this, 'notify'));

        //print 'PUBNUB subscription created' . PHP_EOL;

        return $this;

    }

    /**
     * Attention, this function is NOT PUBLIC!!! The only reason it's public is due to PHP 5.3 limitations
     * @protected
     * @param $pubnubMessage
     * @return bool
     */
    public function notify($pubnubMessage)
    {

        $message = $pubnubMessage['message'];

        //TODO Since pubnub blocks everything this is probably the only place where we can intercept the process and to subscription renew
        //$this->renew();

        if ($this->isSubscribed() && $this->subscription['deliveryMode']['encryption'] && $this->subscription['deliveryMode']['encryptionKey']) {

            $cipher = new Crypt_AES(CRYPT_AES_MODE_ECB);
            $cipher->setKey(base64_decode($this->subscription['deliveryMode']['encryptionKey']));
            $message = $cipher->decrypt(base64_decode($message));
            $message = json_decode($message, true); // PUBNUB always decode as array

        }

        //print 'Message received: ' . $message . PHP_EOL;

        $this->emit(self::EVENT_NOTIFICATION, new NotificationEvent($message));

        return $this->keepPolling;

    }

    /**
     * @return Pubnub|PubnubMock
     */
    public function getPubnub()
    {
        return $this->pubnub;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }

}