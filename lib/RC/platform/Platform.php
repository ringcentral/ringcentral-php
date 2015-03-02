<?php

namespace RC\platform;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Message\Response;
use stdClass;

class Platform
{

    const ACCESS_TOKEN_TTL = 600; // 10 minutes
    const REFRESH_TOKEN_TTL = 36000; // 10 hours
    const REFRESH_TOKEN_TTL_REMEMBER = 604800; // 1 week
    const ACCOUNT_PREFIX = '/account/';
    const ACCOUNT_ID = '~';
    const TOKEN_ENDPOINT = '/restapi/oauth/token';
    const REVOKE_ENDPOINT = '/restapi/oauth/revoke';
    const API_VERSION = 'v1.0';
    const URL_PREFIX = '/restapi';

    protected $server;
    protected $appKey;
    protected $appSecret;

    protected $account = self::ACCOUNT_ID;

    /** @var Auth */
    protected $auth;

    /** @var Client */
    protected $client;

    public function __construct($appKey, $appSecret, $server)
    {

        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->server = $server;

        $this->auth = new Auth();

        $this->client = new Client([
            //'base_url' => $this->server,
            'defaults' => [
                'headers' => [
                    'content-type' => 'application/json',
                    'accept'       => 'application/json'
                ],
            ]
        ]);

        $this->client->getEmitter()->on('before', function (BeforeEvent $event) {

            $request = $event->getRequest();

            if (!$request->getHeader('authorization')) {

                if ($this->isAuthorized()) $request->addHeader('authorization', $this->getAuthHeader());

            }

            $request->setUrl($this->apiUrl($request->getUrl(), ['addServer' => true]));

            //print 'REQUEST:' . PHP_EOL;
            //print '  - Url: ' . $request->getUrl() . PHP_EOL;
            //print '  - Content-Type: ' . $request->getHeader('content-type') . PHP_EOL;

        });

        $this->client->getEmitter()->on('complete', function (CompleteEvent $event) {
        });

    }

    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param array $authData
     * @return $this
     */
    public function setAuthData(array $authData = [])
    {
        $this->auth->setData($authData);
        return $this;
    }

    /**
     * @return array
     */
    public function getAuthData()
    {
        return $this->auth->getData();
    }

    public function isAuthorized($refresh = true)
    {

        if (!$this->auth->isAccessTokenValid()) {
            if ($refresh) {
                print 'Refresh is required' . PHP_EOL;
                $this->refresh();
            }
        }

        if (!$this->auth->isAccessTokenValid()) {
            throw new Exception('Access token is not valid after refresh timeout');
        }

        return $this;

    }

    /**
     * @param string $url
     * @param array  $options
     * @return string
     */
    public function apiUrl($url = '', $options = [])
    {

        $builtUrl = '';

        if ($options['addServer'] && !stristr($url, 'http://') && !stristr($url, 'https://')) {
            $builtUrl .= $this->server;
        }

        if (!stristr($url, self::URL_PREFIX)) {
            $builtUrl .= self::URL_PREFIX . '/' . self::API_VERSION;
        }

        if (stristr($url, self::ACCOUNT_PREFIX)) {
            $builtUrl = str_replace(self::ACCOUNT_PREFIX . self::ACCOUNT_ID, self::ACCOUNT_PREFIX . $this->account,
                $builtUrl);
        }

        $builtUrl .= $url;

        if (!empty($options['addMethod']) || !empty($options['addToken'])) {
            $builtUrl .= (stristr($url, '?') ? '&' : '?');
        }

        if (!empty($options['addMethod'])) {
            $builtUrl .= '_method=' . $options['addMethod'];
        }
        if (!empty($options['addToken'])) {
            $builtUrl .= ($options['addMethod'] ? '&' : '') . 'access_token=' . $this->auth->getAccessToken();
        }

        return $builtUrl;

    }

    /**
     * @param string $username
     * @param string $extension
     * @param string $password
     * @param bool   $remember
     * @return \GuzzleHttp\Message\Response
     */
    public function authorize($username = '', $extension = '', $password = '', $remember = false)
    {

        $response = $this->authCall([], [
            'grant_type'        => 'password',
            'username'          => $username,
            'extension'         => $extension ? $extension : null,
            'password'          => $password,
            'access_token_ttl'  => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => $remember ? self::REFRESH_TOKEN_TTL_REMEMBER : self::REFRESH_TOKEN_TTL
        ]);

        $this->auth
            ->setData($response->json())
            ->setRemember($remember);

        return $response;

    }

    /**
     * @return \GuzzleHttp\Message\Response
     * @throws Exception
     */
    public function refresh()
    {

        if (!$this->auth->isRefreshTokenValid()) {
            throw new Exception('Refresh token has expired');
        }

        // Synchronous
        $response = $this->authCall([], [
            "grant_type"        => "refresh_token",
            "refresh_token"     => $this->auth->getRefreshToken(),
            "access_token_ttl"  => self::ACCESS_TOKEN_TTL,
            "refresh_token_ttl" => $this->auth->isRemember() ? self::REFRESH_TOKEN_TTL_REMEMBER : self::REFRESH_TOKEN_TTL
        ]);

        $this->auth->setData($response->json());

        return $response;

    }

    /**
     * @return Response
     * @throws Exception
     */
    public function logout()
    {

        $response = $this->authCall([
            'token' => $this->auth->getAccessToken()
        ]);

        $this->auth->reset();

        return $response;

    }

    protected function getApiKey()
    {
        return base64_encode($this->appKey . ':' . $this->appSecret);
    }

    protected function getAuthHeader()
    {
        return $this->auth->getTokenType() . ' ' . $this->auth->getAccessToken();
    }

    protected function authCall(array $queryParams = [], array $body = [])
    {

        return $this->client->post(self::TOKEN_ENDPOINT, [
            'headers' => [
                'authorization' => 'Basic ' . $this->getApiKey(),
                'content-type'  => 'application/x-www-form-urlencoded',
            ],
            'body'    => $body,
            'query'   => $queryParams
        ]);

    }

}