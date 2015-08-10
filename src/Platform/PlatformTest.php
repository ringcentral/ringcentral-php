<?php

use GuzzleHttp\Psr7\Request;
use RingCentral\SDK\Mocks\GenericMock;
use RingCentral\SDK\Mocks\LogoutMock;
use RingCentral\SDK\Mocks\RefreshMock;
use RingCentral\SDK\Test\TestCase;

class PlatformTest extends TestCase
{

    public function testKey()
    {

        $sdk = $this->getSDK(false);

        $this->assertEquals('d2hhdGV2ZXI6d2hhdGV2ZXI=', $sdk->getPlatform()->getApiKey());

    }

    public function testLogin()
    {

        $sdk = $this->getSDK();
        $authData = $sdk->getPlatform()->getAuthData();

        $this->assertTrue(!empty($authData['remember']));

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Refresh token has expired
     */
    public function testRefreshWithOutdatedToken()
    {

        $sdk = $this->getSDK(true);

        $sdk->getClient()->getMockRegistry()
            ->add(new RefreshMock());

        $sdk->getPlatform()
            ->setAuthData(array(
                'refresh_token_expires_in'  => 1,
                'refresh_token_expire_time' => 1
            ))
            ->refresh();

    }

    public function testAutomaticRefresh()
    {

        $sdk = $this->getSDK();

        $sdk->getClient()->getMockRegistry()
            ->add(new RefreshMock())
            ->add(new GenericMock('/foo', array('foo' => 'bar')));

        $sdk->getPlatform()->setAuthData(array(
            'expires_in'  => 1,
            'expire_time' => 1
        ));

        $this->assertEquals('bar', $sdk->getPlatform()->get('/foo')->getJson()->foo);

        $authData = $sdk->getPlatform()->getAuthData();

        $this->assertEquals('ACCESS_TOKEN_FROM_REFRESH', $authData['access_token']);

    }

    public function testLogout()
    {

        $sdk = $this->getSDK();

        $sdk->getClient()->getMockRegistry()
            ->add(new LogoutMock());

        $sdk->getPlatform()->logout();

        $authData = $sdk->getPlatform()->getAuthData();

        $this->assertEquals('', $authData['access_token']);
        $this->assertEquals('', $authData['refresh_token']);

    }

    public function testApiUrl()
    {

        $sdk = $this->getSDK();

        $this->assertEquals(
            'https://whatever/restapi/v1.0/account/~/extension/~?_method=POST&access_token=ACCESS_TOKEN',
            $sdk->getPlatform()->apiUrl('/account/~/extension/~', array(
                'addServer' => true,
                'addMethod' => 'POST',
                'addToken'  => true
            ))
        );

        $this->assertEquals(
            'https://foo/account/~/extension/~?_method=POST&access_token=ACCESS_TOKEN',
            $sdk->getPlatform()->apiUrl('https://foo/account/~/extension/~', array(
                'addServer' => true,
                'addMethod' => 'POST',
                'addToken'  => true
            ))
        );

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Access token is not valid after refresh timeout
     */
    public function testUnsuccessfulRefresh()
    {

        $sdk = $this->getSDK();

        $sdk->getClient()->getMockRegistry()
            ->add(new RefreshMock(false, -1))
            ->add(new GenericMock('/foo', array('foo' => 'bar')));

        $sdk->getPlatform()->setAuthData(array(
            'expires_in'  => 1,
            'expire_time' => 1
        ));

        $sdk->getPlatform()->isAuthorized();

    }

    public function testProcessRequest()
    {

        $sdk = $this->getSDK();

        $sdk->getClient()->getMockRegistry()
            ->add(new GenericMock('/foo', array('foo' => 'bar')));

        $request = $sdk->getPlatform()->processRequest(new Request('GET', '/foo'));

        $this->assertEquals('https://whatever/restapi/v1.0/foo', (string)$request->getUri());

    }

}