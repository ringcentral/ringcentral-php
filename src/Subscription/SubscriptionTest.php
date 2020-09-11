<?php

use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\SDK;
use RingCentral\SDK\Subscription\Events\ErrorEvent;
use RingCentral\SDK\Subscription\Events\NotificationEvent;
use RingCentral\SDK\Subscription\Events\SuccessEvent;
use RingCentral\SDK\Subscription\Subscription;
use RingCentral\SDK\Test\TestCase;
use PubNub\Models\Consumer\PubSub\PNMessageResult;

class SubscriptionTest extends TestCase
{

    /**
     * @param $sdk
     * @return Subscription
     */
    protected function createSubscription(SDK $sdk)
    {
        $s = $sdk->createSubscription();
        $s->setSkipSubscribe(true);
        return $s;
    }

    public function testPresenceDecryption()
    {

        $sdk = $this->getSDK([
            $this->presenceSubscriptionMock()
        ]);

        $executed = false;
        $aesMessage = 'gkw8EU4G1SDVa2/hrlv6+0ViIxB7N1i1z5MU/Hu2xkIKzH6yQzhr3vIc27IAN558kTOkacqE5DkLpRdnN1orwtIBsUHmPM' .
                      'kMWTOLDzVr6eRk+2Gcj2Wft7ZKrCD+FCXlKYIoa98tUD2xvoYnRwxiE2QaNywl8UtjaqpTk1+WDImBrt6uabB1WICY/qE0' .
                      'It3DqQ6vdUWISoTfjb+vT5h9kfZxWYUP4ykN2UtUW1biqCjj1Rb6GWGnTx6jPqF77ud0XgV1rk/Q6heSFZWV/GP23/iytD' .
                      'PK1HGJoJqXPx7ErQU=';

        $t = $this;

        $s = $this->createSubscription($sdk);

        $s->addEvents(['/restapi/v1.0/account/~/extension/1/presence'])
          ->addListener(Subscription::EVENT_NOTIFICATION, function (NotificationEvent $e) use (&$executed, &$t) {

              $expected = [
                  "timestamp" => "2014-03-12T20:47:54.712+0000",
                  "body"      => [
                      "extensionId"     => 402853446008,
                      "telephonyStatus" => "OnHold"
                  ],
                  "event"     => "/restapi/v1.0/account/~/extension/402853446008/presence",
                  "uuid"      => "db01e7de-5f3c-4ee5-ab72-f8bd3b77e308"
              ];

              $t->assertEquals($expected, $e->payload());

              $executed = true;

          });

        $s->register();

        $result = new PNMessageResult($aesMessage, NULL, NULL, NULL, NULL);

        $s->notify($result);

        $this->assertTrue($executed, 'make sure that callback has been called');

    }

    public function testPlainSubscription()
    {

        $sdk = $this->getSDK([
            $this->subscriptionMock()
        ]);

        $executed = false;

        $expected = [
            "timestamp" => "2014-03-12T20:47:54.712+0000",
            "body"      => [
                "extensionId"     => 402853446008,
                "telephonyStatus" => "OnHold"
            ],
            "event"     => "/restapi/v1.0/account/~/extension/402853446008/presence",
            "uuid"      => "db01e7de-5f3c-4ee5-ab72-f8bd3b77e308"
        ];

        $t = $this;

        $s = $this->createSubscription($sdk);

        $s->addEvents(['/restapi/v1.0/account/~/extension/1/presence'])
          ->addListener(Subscription::EVENT_NOTIFICATION,
              function (NotificationEvent $e) use (&$executed, $expected, &$t) {

                  $t->assertEquals($expected, $e->payload());

                  $executed = true;

              });

        $s->register();

        $result = new PNMessageResult(array_merge([], $expected), NULL, NULL, NULL, NULL);

        $s->notify($result);

        $this->assertTrue($executed, 'make sure that callback has been called');

    }

    public function testSubscribeWithEvents()
    {

        $sdk = $this->getSDK([
            $this->subscriptionMock()
        ]);

        $s = $this->createSubscription($sdk);
        $res = $s->register(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);

        $this->assertEquals('/restapi/v1.0/account/~/extension/1/presence', $res->json()->eventFilters[0]);

    }

    public function testSubscribeErrorWithEvents()
    {

        $this->expectException(\RingCentral\SDK\Http\ApiException::class);
        $this->expectExceptionMessage('Expected Error');

        $sdk = $this->getSDK([
            $this->createResponse('POST', '/subscription', ['message' => 'Expected Error'], 400)
        ]);

        $this->createSubscription($sdk)
             ->register(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);

    }

    public function testEvents()
    {

        $spy = false;

        $sdk = $this->getSDK([
            $this->subscriptionMock()
        ]);

        $self = $this;

        $s = $this->createSubscription($sdk);

        $s->addListener(Subscription::EVENT_SUBSCRIBE_SUCCESS, function (SuccessEvent $event) use (&$self, &$spy) {
            $self->assertEquals('/restapi/v1.0/account/~/extension/1/presence',
                $event->apiResponse()->json()->eventFilters[0]);
            $spy = true;
        });

        $s->register(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);

        $this->assertEquals(true, $spy);

    }

    public function testEventsFail()
    {

        $spy = false;

        $sdk = $this->getSDK([
            $this->createResponse('POST', '/subscription', ['message' => 'Expected Error'], 400)
        ]);

        $self = $this;

        $s = $this->createSubscription($sdk);

        $s->addListener(Subscription::EVENT_SUBSCRIBE_ERROR, function (ErrorEvent $event) use (&$self, &$spy) {
            //$self->assertEquals('Expected Error', $event->exception()->getMessage());
            $spy = true;
        });

        try {
            $s->register(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);
        } catch (ApiException $e) {
        }

        $this->assertEquals(true, $spy);

    }

    public function testRenew()
    {

        $sdk = $this->getSDK([
            $this->subscriptionMock(),
            $this->createResponse('PUT', '/subscription/foo-bar-baz', ['ok' => 'ok'])
        ]);

        $s = $this->createSubscription($sdk);

        $s->subscribe(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);
        $s->renew(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);

        $this->assertEquals(['ok' => 'ok'], $s->subscription());

    }

    public function testRenewError()
    {

        $this->expectException(\RingCentral\SDK\Http\ApiException::class);
        $this->expectExceptionMessage('Expected Error');

        $sdk = $this->getSDK([
            $this->subscriptionMock(),
            $this->createResponse('PUT', '/subscription/foo-bar-baz', ['message' => 'Expected Error'], 400)
        ]);

        $s = $this->createSubscription($sdk);
        $s->subscribe(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);
        $s->renew(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);

    }

    public function testRegister()
    {

        $sdk = $this->getSDK([
            $this->subscriptionMock(),
            $this->createResponse('PUT', '/subscription/foo-bar-baz', ['ok' => 'ok'])
        ]);

        $s = $this->createSubscription($sdk);

        $s->register(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);
        $s->register(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);

        $this->assertEquals(['ok' => 'ok'], $s->subscription());

    }

    public function testRemove()
    {

        $sdk = $this->getSDK([
            $this->subscriptionMock(),
            $this->createResponse('DELETE', '/subscription/foo-bar-baz', ['ok' => 'ok'])
        ]);

        $s = $this->createSubscription($sdk);
        $s->subscribe(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);
        $s->remove();

        $this->assertEquals(null, $s->subscription());

    }

    public function testRemoveError()
    {
        $this->expectException(\RingCentral\SDK\Http\ApiException::class);
        $this->expectExceptionMessage('Expected Error');

        $sdk = $this->getSDK([
            $this->subscriptionMock(),
            $this->createResponse('DELETE', '/subscription/foo-bar-baz', ['message' => 'Expected Error'], 400)
        ]);

        $s = $this->createSubscription($sdk);
        $s->subscribe(['events' => ['/restapi/v1.0/account/~/extension/1/presence']]);
        $s->remove();

    }

    public function testKeepPolling()
    {

        $sdk = $this->getSDK();

        $s = $this->createSubscription($sdk);
        $this->assertEquals(false, $s->keepPolling());
        $s->setKeepPolling(true);
        $this->assertEquals(true, $s->keepPolling());

    }

}