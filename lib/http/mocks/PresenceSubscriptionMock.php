<?php

namespace RingCentral\http\mocks;

use RingCentral\http\Request;
use RingCentral\http\Response;

class PresenceSubscriptionMock extends Mock
{

    protected $path = '/restapi/v1.0/subscription';

    protected $id = '1';
    protected $detailed = false;

    public function __construct($id = '1', $detailed = true)
    {
        $this->detailed = !!$detailed;
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(Request $request)
    {

        $expiresIn = 15 * 60 * 60;

        return new Response(200, self::createBody(array(
            'eventFilters'   => array('/restapi/v1.0/account/~/extension/' . $this->id . '/presence' . ($this->detailed ? '?detailedTelephonyState=true' : '')),
            'expirationTime' => date('c', time() + $expiresIn),
            'expiresIn'      => $expiresIn,
            'deliveryMode'   => array(
                'transportType'       => 'PubNub',
                'encryption'          => true,
                'address'             => '123_foo',
                'subscriberKey'       => 'sub-c-foo',
                'secretKey'           => 'sec-c-bar',
                'encryptionAlgorithm' => 'AES',
                'encryptionKey'       => 'e0bMTqmumPfFUbwzppkSbA=='
            ),
            'creationTime'   => date('c'),
            'id'             => 'foo-bar-baz',
            'status'         => 'Active',
            'uri'            => 'https=>//platform.ringcentral.com/restapi/v1.0/subscription/foo-bar-baz'
        )));

    }

}