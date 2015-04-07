<?php

namespace RC\http\mocks;

use RC\http\Request;
use RC\http\Response;

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

        return new Response(200, self::createBody(array(
            'eventFilters'   => array('/restapi/v1.0/account/~/extension/' . $this->id . '/presence' . ($this->detailed ? '?detailedTelephonyState=true' : '')),
            'expirationTime' => date('c', time() + (15 * 60 * 60)),
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