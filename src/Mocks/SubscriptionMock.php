<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;
use RingCentral\SDK\Core\Utils;

class SubscriptionMock extends AbstractMock
{

    protected $path = '/restapi/v1.0/subscription';

    protected $expiresIn = 54000; // 15 * 60 * 60

    public function __construct($expiresIn = 54000)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(RequestInterface $request)
    {

        $body = Utils::json_parse((string)$request->getBody(), true);

        return self::createBody(array(
            'eventFilters'   => $body['eventFilters'],
            'expirationTime' => date('c', time() + $this->expiresIn),
            'expiresIn'      => $this->expiresIn,
            'deliveryMode'   => array(
                'transportType' => 'PubNub',
                'encryption'    => false,
                'address'       => '123_foo',
                'subscriberKey' => 'sub-c-foo',
                'secretKey'     => 'sec-c-bar'
            ),
            'id'             => 'foo-bar-baz',
            'creationTime'   => date('c'),
            'status'         => 'Active',
            'uri'            => 'https=>//platform.ringcentral.com/restapi/v1.0/subscription/foo-bar-baz'
        ));

    }

}