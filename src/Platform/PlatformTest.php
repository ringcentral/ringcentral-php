<?php

use GuzzleHttp\Psr7\Request;
use RingCentral\SDK\Mocks\Mock;
use RingCentral\SDK\SDK;
use RingCentral\SDK\Test\TestCase;

class PlatformTest extends TestCase
{

    public function testLogin()
    {

        $sdk = $this->getSDK();
        $authData = $sdk->platform()->auth()->data();
        $this->assertTrue(!empty($authData['access_token']));

    }

    public function testRefreshWithOutdatedToken()
    {

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Refresh token has expired');

        $sdk = $this->getSDK();

        $sdk->platform()->auth()->setData([
            'refresh_token_expires_in'  => 1,
            'refresh_token_expire_time' => 1
        ]);

        $sdk->platform()->refresh();

    }

    public function testAutomaticRefresh()
    {

        $sdk = $this->getSDK([
            $this->refreshMock(),
            $this->createResponse('GET', '/foo', ['foo' => 'bar'])
        ]);

        $sdk->platform()->auth()->setData([
            'expires_in'  => 1,
            'expire_time' => 1
        ]);

        $this->assertEquals('bar', $sdk->platform()->get('/foo')->json()->foo);

        $this->assertEquals('ACCESS_TOKEN_FROM_REFRESH', $sdk->platform()->auth()->accessToken());
        $this->assertTrue($sdk->platform()->loggedIn());

    }

    public function testLogout()
    {

        $sdk = $this->getSDK([
            $this->logoutMock()
        ]);

        $sdk->platform()->logout();

        $authData = $sdk->platform()->auth()->data();

        $this->assertEquals('', $authData['access_token']);
        $this->assertEquals('', $authData['refresh_token']);

    }

    public function testApiUrl()
    {

        $sdk = $this->getSDK();

        $this->assertEquals(
            'https://whatever/restapi/v1.0/account/~/extension/~?_method=POST&access_token=ACCESS_TOKEN',
            $sdk->platform()->createUrl('/account/~/extension/~', [
                'addServer' => true,
                'addMethod' => 'POST',
                'addToken'  => true
            ])
        );

        $this->assertEquals(
            'https://foo/account/~/extension/~?_method=POST&access_token=ACCESS_TOKEN',
            $sdk->platform()->createUrl('https://foo/account/~/extension/~', [
                'addServer' => true,
                'addMethod' => 'POST',
                'addToken'  => true
            ])
        );

    }

    public function testProcessRequest()
    {

        $sdk = $this->getSDK([
            $this->createResponse('GET', '/foo', ['foo' => 'bar'])
        ]);

        $request = $sdk->platform()->inflateRequest(new Request('GET', '/foo'));

        $this->assertEquals('https://whatever/restapi/v1.0/foo', (string)$request->getUri());

        $this->assertEquals($request->getHeaderLine('User-Agent'), $request->getHeaderLine('RC-User-Agent'));

        $this->assertTrue(!!$request->getHeaderLine('User-Agent'));
        $this->assertStringContainsString('RCPHPSDK/' . SDK::VERSION, $request->getHeaderLine('User-Agent'));
        $this->assertStringContainsString('SDKTests/' . SDK::VERSION, $request->getHeaderLine('User-Agent'));

    }

}