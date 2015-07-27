<?php

use RingCentral\http\mocks\GenericMock;
use RingCentral\http\mocks\LogoutMock;
use RingCentral\http\mocks\RefreshMock;
use RingCentral\test\TestCase;

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

    public function testRefreshWithOutdatedToken()
    {

        $sdk = $this->getSDK(true);

        $sdk->getContext()
            ->getMocks()
            ->add(new RefreshMock());

        $sdk->getPlatform()->setAuthData(array(
            'refresh_token_expires_in'  => 1,
            'refresh_token_expire_time' => 1
        ));

        $caught = false;

        try {
            $sdk->getPlatform()->refresh();
        } catch (Exception $e) {
            $caught = true;
            $this->assertEquals('Refresh token has expired', $e->getMessage());
        }

        $this->assertTrue($caught);

    }

    public function testAutomaticRefresh()
    {

        $sdk = $this->getSDK();

        $sdk->getContext()
            ->getMocks()
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

        $sdk->getContext()
            ->getMocks()
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

    public function testUnsuccessfulRefresh(){

        $sdk = $this->getSDK();

        $sdk->getContext()
            ->getMocks()
            ->add(new RefreshMock(false, -1))
            ->add(new GenericMock('/foo', array('foo' => 'bar')));

        $sdk->getPlatform()->setAuthData(array(
            'expires_in'  => 1,
            'expire_time' => 1
        ));

        $caught = false;

        try {
            $sdk->getPlatform()->isAuthorized();
        } catch (Exception $e) {
            $this->assertEquals('Access token is not valid after refresh timeout', $e->getMessage());
            $caught = true;
        }

        $this->assertTrue($caught);

    }

}