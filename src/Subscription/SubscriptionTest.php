<?php

use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\Mocks\Mock;
use RingCentral\SDK\Subscription\Events\ErrorEvent;
use RingCentral\SDK\Subscription\Events\NotificationEvent;
use RingCentral\SDK\Subscription\Events\SuccessEvent;
use RingCentral\SDK\Subscription\Subscription;
use RingCentral\SDK\Test\TestCase;

class SubscriptionTest extends TestCase
{

    public function testPresenceDecryption()
    {

        $sdk = $this->getSDK();

        $sdk->mockRegistry()->presenceSubscriptionMock();

        $executed = false;
        $aesMessage = 'gkw8EU4G1SDVa2/hrlv6+0ViIxB7N1i1z5MU/Hu2xkIKzH6yQzhr3vIc27IAN558kTOkacqE5DkLpRdnN1orwtIBsUHmPM' .
                      'kMWTOLDzVr6eRk+2Gcj2Wft7ZKrCD+FCXlKYIoa98tUD2xvoYnRwxiE2QaNywl8UtjaqpTk1+WDImBrt6uabB1WICY/qE0' .
                      'It3DqQ6vdUWISoTfjb+vT5h9kfZxWYUP4ykN2UtUW1biqCjj1Rb6GWGnTx6jPqF77ud0XgV1rk/Q6heSFZWV/GP23/iytD' .
                      'PK1HGJoJqXPx7ErQU=';

        $t = $this;

        $s = $sdk->createSubscription();

        $s->addEvents(array('/restapi/v1.0/account/~/extension/1/presence'))
          ->addListener(Subscription::EVENT_NOTIFICATION, function (NotificationEvent $e) use (&$executed, &$t) {

              $expected = array(
                  "timestamp" => "2014-03-12T20:47:54.712+0000",
                  "body"      => array(
                      "extensionId"     => 402853446008,
                      "telephonyStatus" => "OnHold"
                  ),
                  "event"     => "/restapi/v1.0/account/~/extension/402853446008/presence",
                  "uuid"      => "db01e7de-5f3c-4ee5-ab72-f8bd3b77e308"
              );

              $t->assertEquals($expected, $e->payload());

              $executed = true;

          });

        $s->register();

        $s->pubnub()->receiveMessage($aesMessage);

        $this->assertTrue($executed, 'make sure that callback has been called');

    }

    public function testPlainSubscription()
    {

        $sdk = $this->getSDK();

        $sdk->mockRegistry()->subscriptionMock();

        $executed = false;

        $expected = array(
            "timestamp" => "2014-03-12T20:47:54.712+0000",
            "body"      => array(
                "extensionId"     => 402853446008,
                "telephonyStatus" => "OnHold"
            ),
            "event"     => "/restapi/v1.0/account/~/extension/402853446008/presence",
            "uuid"      => "db01e7de-5f3c-4ee5-ab72-f8bd3b77e308"
        );

        $t = $this;

        $s = $sdk->createSubscription();

        $s->addEvents(array('/restapi/v1.0/account/~/extension/1/presence'))
          ->addListener(Subscription::EVENT_NOTIFICATION,
              function (NotificationEvent $e) use (&$executed, $expected, &$t) {

                  $t->assertEquals($expected, $e->payload());

                  $executed = true;

              });

        $s->register();

        $s->pubnub()->receiveMessage(array_merge(array(), $expected));

        $this->assertTrue($executed, 'make sure that callback has been called');

    }

    public function testSubscribeWithEvents()
    {

        $sdk = $this->getSDK();

        $sdk->mockRegistry()->subscriptionMock();

        $s = $sdk->createSubscription()
                 ->register(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));

        $this->assertEquals('/restapi/v1.0/account/~/extension/1/presence', $s->json()->eventFilters[0]);

    }

    /**
     * @expectedException \RingCentral\SDK\Http\ApiException
     * @expectedExceptionMessage Expected Error
     */
    public function testSubscribeErrorWithEvents()
    {

        $sdk = $this->getSDK();

        $sdk->mockRegistry()->add(new Mock('POST', '/subscription', array('message' => 'Expected Error'), 400));

        $sdk->createSubscription()
            ->register(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));

    }

    public function testEvents()
    {

        $counter = 0;

        $sdk = $this->getSDK();
        $self = $this;

        $sdk->mockRegistry()->subscriptionMock();

        $s1 = $sdk->createSubscription();

        $s1->addListener(Subscription::EVENT_SUBSCRIBE_SUCCESS, function (SuccessEvent $event) use (&$self, &$counter) {
            $self->assertEquals('/restapi/v1.0/account/~/extension/1/presence',
                $event->apiResponse()->json()->eventFilters[0]);
            $counter++;
        });

        $s1->register(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));

        $sdk->mockRegistry()->clear()->add(new Mock('POST', '/subscription', array('message' => 'Expected Error'), 400));

        $s2 = $sdk->createSubscription();

        $s2->addListener(Subscription::EVENT_SUBSCRIBE_ERROR, function (ErrorEvent $event) use (&$self, &$counter) {
            $self->assertEquals('Expected Error', $event->exception()->getMessage());
            $counter++;
        });

        try {
            $s2->register(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));
        } catch (ApiException $e) {
        }

        $this->assertEquals(2, $counter); // make sure both callbacks were used

    }

    public function testRenew()
    {

        $sdk = $this->getSDK();

        $sdk->mockRegistry()
            ->subscriptionMock()
            ->add(new Mock('PUT', '/subscription/foo-bar-baz', array('ok' => 'ok')));

        $s = $sdk->createSubscription();

        $s->subscribe(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));
        $s->renew(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));

        $this->assertEquals(array('ok' => 'ok'), $s->subscription());

    }

    /**
     * @expectedException \RingCentral\SDK\Http\ApiException
     * @expectedExceptionMessage Expected Error
     */
    public function testRenewError()
    {

        $sdk = $this->getSDK();

        $sdk->mockRegistry()
            ->subscriptionMock()
            ->add(new Mock('PUT', '/subscription/foo-bar-baz', array('message' => 'Expected Error'), 400));

        $s = $sdk->createSubscription();
        $s->subscribe(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));
        $s->renew(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));

    }

    public function testRegister()
    {

        $sdk = $this->getSDK();

        $sdk->mockRegistry()->subscriptionMock();

        $s = $sdk->createSubscription();

        $s->register(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));

        $sdk->mockRegistry()
            ->add(new Mock('PUT', '/subscription/foo-bar-baz', array('ok' => 'ok')));

        $s->register(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));

        $this->assertEquals(array('ok' => 'ok'), $s->subscription());

    }

    public function testRemove()
    {

        $sdk = $this->getSDK();

        $sdk->mockRegistry()
            ->subscriptionMock()
            ->add(new Mock('DELETE', '/subscription/foo-bar-baz', array('ok' => 'ok')));

        $s = $sdk->createSubscription();
        $s->subscribe(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));
        $s->remove();

        $this->assertEquals(null, $s->subscription());

    }

    /**
     * @expectedException \RingCentral\SDK\Http\ApiException
     * @expectedExceptionMessage Expected Error
     */
    public function testRemoveError()
    {

        $sdk = $this->getSDK();

        $sdk->mockRegistry()
            ->subscriptionMock()
            ->add(new Mock('DELETE', '/subscription/foo-bar-baz', array('message' => 'Expected Error'), 400));

        $s = $sdk->createSubscription();
        $s->subscribe(array('events' => array('/restapi/v1.0/account/~/extension/1/presence')));
        $s->remove();

    }

    public function testKeepPolling()
    {

        $sdk = $this->getSDK();

        $s = $sdk->createSubscription();
        $this->assertEquals(false, $s->keepPolling());
        $s->setKeepPolling(true);
        $this->assertEquals(true, $s->keepPolling());

    }

}