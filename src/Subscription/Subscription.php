<?php

namespace RingCentral\SDK\Subscription;

use Exception;
use Pubnub\Pubnub;
use Pubnub\PubnubAES;
use RingCentral\SDK\Core\Utils;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\Platform\Platform;
use RingCentral\SDK\pubnub\PubnubFactory;
use RingCentral\SDK\Pubnub\PubnubMock;
use RingCentral\SDK\Subscription\Events\ErrorEvent;
use RingCentral\SDK\Subscription\Events\NotificationEvent;
use RingCentral\SDK\Subscription\Events\SuccessEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Subscription extends EventDispatcher
{

    const EVENT_NOTIFICATION = 'notification';
    const EVENT_REMOVE_SUCCESS = 'removeSuccess';
    const EVENT_REMOVE_ERROR = 'removeError';
    const EVENT_RENEW_SUCCESS = 'renewSuccess';
    const EVENT_RENEW_ERROR = 'renewError';
    const EVENT_SUBSCRIBE_SUCCESS = 'subscribeSuccess';
    const EVENT_SUBSCRIBE_ERROR = 'subscribeError';

    /** @var Platform */
    protected $_platform;

    /** @var string[] */
    protected $_eventFilters = array();

    protected $_subscription = array(
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
    protected $_pubnub;

    /** @var PubnubFactory */
    protected $_pubnubFactory;

    protected $_keepPolling = false;

    function __construct(PubnubFactory $pubnubFactory, Platform $platform)
    {

        $this->_platform = $platform;
        $this->_pubnubFactory = $pubnubFactory;

    }

    /**
     * @return Pubnub|PubnubMock
     */
    function pubnub()
    {
        return $this->_pubnub;
    }

    /**
     * @param array $options
     * @return ApiResponse
     * @throws Exception
     */
    function register(array $options = array())
    {
        if ($this->alive()) {
            return $this->renew($options);
        } else {
            return $this->subscribe($options);
        }
    }

    function setKeepPolling($flag = false)
    {
        $this->_keepPolling = !empty($flag);
    }

    function keepPolling()
    {
        return $this->_keepPolling;
    }

    function addEvents(array $events)
    {
        $this->_eventFilters = array_merge($this->_eventFilters, $events);
        return $this;
    }

    function setEvents(array $events)
    {
        $this->_eventFilters = $events;
        return $this;
    }

    function subscribe(array $options = array())
    {

        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        try {

            $response = $this->_platform->post('/restapi/v1.0/subscription', array(
                'eventFilters' => $this->getFullEventFilters(),
                'deliveryMode' => array(
                    'transportType' => 'PubNub'
                )
            ));

            $this->setSubscription($response->jsonArray());
            $this->subscribeAtPubnub();

            //TODO Subscription renewal when everything will become async

            $this->dispatch(self::EVENT_SUBSCRIBE_SUCCESS, new SuccessEvent($response));

            return $response;

        } catch (Exception $e) {

            $this->reset();
            $this->dispatch(self::EVENT_SUBSCRIBE_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    function renew(array $options = array())
    {

        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        if (!$this->alive()) {
            throw new Exception('Subscription is not alive');
        }

        try {

            $response = $this->_platform->put('/restapi/v1.0/subscription/' . $this->_subscription['id'], array(
                'eventFilters' => $this->getFullEventFilters()
            ));

            $this->setSubscription($response->jsonArray());

            $this->dispatch(self::EVENT_RENEW_SUCCESS, new SuccessEvent($response));

            return $this;

        } catch (Exception $e) {

            $this->reset();
            $this->dispatch(self::EVENT_RENEW_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    function remove()
    {

        if (!$this->alive()) {
            throw new Exception('Subscription is not alive');
        }

        try {

            $response = $this->_platform->delete('/restapi/v1.0/subscription/' . $this->_subscription['id']);

            $this->reset();

            $this->dispatch(self::EVENT_REMOVE_SUCCESS, new SuccessEvent($response));

            return $response;

        } catch (Exception $e) {

            $this->reset();
            $this->dispatch(self::EVENT_REMOVE_ERROR, new ErrorEvent($e));
            throw $e;

        }

    }

    function alive()
    {
        return (!empty($this->_subscription) &&
                !empty($this->_subscription['deliveryMode']) &&
                !empty($this->_subscription['deliveryMode']['subscriberKey']) &&
                !empty($this->_subscription['deliveryMode']['address']));
    }


    function subscription()
    {
        return $this->_subscription;
    }

    function setSubscription($subscription)
    {
        $this->_subscription = $subscription;
        return $this;
    }

    function reset()
    {

        if ($this->_pubnub && $this->alive()) {
            //$this->_pubnub->unsubscribe($this->subscription['deliveryMode']['address']);
            $this->_pubnub = null;
        }

        $this->_subscription = null;

    }


    protected function subscribeAtPubnub()
    {

        if (!$this->alive()) {
            throw new Exception('Subscription is not alive');
        }

        $this->_pubnub = $this->_pubnubFactory->pubnub(array(
            'publish_key'   => 'convince-pubnub-its-okay',
            'subscribe_key' => $this->_subscription['deliveryMode']['subscriberKey']
        ));

        $this->_pubnub->subscribe($this->_subscription['deliveryMode']['address'], array($this, 'notify'));

        return $this;

    }

    /**
     * Attention, this function is NOT PUBLIC!!! The only reason it's public is due to PHP 5.3 limitations
     * @protected
     * @param $pubnubMessage
     * @return bool
     * @throws Exception
     */
    public function notify($pubnubMessage)
    {

        $message = $pubnubMessage['message'];

        //TODO Since pubnub blocks everything this is probably the only place where we can intercept the process and do subscription renew
        //$this->renew();

        $message = $this->decrypt($message);
        //print 'Message received: ' . $message . PHP_EOL;

        $this->dispatch(self::EVENT_NOTIFICATION, new NotificationEvent($message));

        return $this->_keepPolling;

    }

    protected function decrypt($message)
    {

        if (!$this->alive()) {
            throw new Exception('Subscription is not alive');
        }

        if ($this->_subscription['deliveryMode']['encryption'] && $this->_subscription['deliveryMode']['encryptionKey']) {

            $aes = new PubnubAES();

            $message = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,
                base64_decode($this->_subscription['deliveryMode']['encryptionKey']),
                base64_decode($message),
                MCRYPT_MODE_ECB);

            $message = Utils::json_parse($aes->unPadPKCS7($message, 128), true); // PUBNUB itself always decode as array

        }

        return $message;

    }

    protected function getFullEventFilters()
    {
        $events = array();
        foreach ($this->_eventFilters as $event) {
            $events[] = $this->_platform->createUrl($event);
        }
        return $events;
    }


}