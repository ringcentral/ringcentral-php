<?php

namespace RingCentral\SDK\Mocks;

use Psr\Http\Message\RequestInterface;

class Registry
{

    /** @var Mock[] */
    protected $responses = array();

    function add(Mock $requestMockResponse)
    {

        $this->responses[] = $requestMockResponse;
        return $this;

    }

    /**
     * @param RequestInterface $request
     * @return Mock
     * @throws \Exception
     */
    function find(RequestInterface $request)
    {

        /** @var Mock $mock */
        $mock = array_shift($this->responses);

        if (empty($mock)) {
            throw new \Exception(sprintf(
                'No mock in registry for request %s %s',
                $request->getMethod(), $request->getUri()
            ));
        }

        if (!$mock->test($request)) {
            throw new \Exception(sprintf(
                'Wrong request %s %s for expected mock %s %s',
                $request->getMethod(), $request->getUri(), $mock->method(), $mock->path()
            ));
        }

        return $mock;

    }

    function clear()
    {
        $this->responses = array();
        return $this;
    }

    function authenticationMock()
    {
        return $this->add(new Mock('POST', '/restapi/oauth/token', array(
            'access_token'             => 'ACCESS_TOKEN',
            'token_type'               => 'bearer',
            'expires_in'               => 3600,
            'refresh_token'            => 'REFRESH_TOKEN',
            'refresh_token_expires_in' => 60480,
            'scope'                    => 'SMS RCM Foo Boo',
            'expireTime'               => time() + 3600,
            'owner_id'                 => 'foo'
        )));
    }

    function logoutMock()
    {
        return $this->add(new Mock('POST', '/restapi/oauth/revoke', array()));
    }

    function presenceSubscriptionMock($id = '1', $detailed = true)
    {

        $expiresIn = 15 * 60 * 60;

        return $this->add(new Mock('POST', '/restapi/v1.0/subscription', array(
            'eventFilters'   => array('/restapi/v1.0/account/~/extension/' . $id . '/presence' . ($detailed ? '?detailedTelephonyState=true' : '')),
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

    function refreshMock($failure = false, $expiresIn = 3600)
    {

        $body = !$failure
            ? array(
                'access_token'             => 'ACCESS_TOKEN_FROM_REFRESH',
                'token_type'               => 'bearer',
                'expires_in'               => $expiresIn,
                'refresh_token'            => 'REFRESH_TOKEN_FROM_REFRESH',
                'refresh_token_expires_in' => 60480,
                'scope'                    => 'SMS RCM Foo Boo',
                'expireTime'               => time() + 3600,
                'owner_id'                 => 'foo'
            )
            : array('message' => 'Wrong token (mock)');

        $status = !$failure ? 200 : 400;

        return $this->add(new Mock('POST', '/restapi/oauth/token', $body, $status));

    }

    function subscriptionMock(
        $expiresIn = 54000,
        array $eventFilters = array('/restapi/v1.0/account/~/extension/1/presence')
    ) {

        return $this->add(new Mock('POST', '/restapi/v1.0/subscription', array(
            'eventFilters'   => $eventFilters,
            'expirationTime' => date('c', time() + $expiresIn),
            'expiresIn'      => $expiresIn,
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
        )));

    }

}