<?php

namespace RC\platform;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Message\RequestInterface;
use RC\platform\http\MessageFactory;
use RC\platform\http\Response;

class Platform extends Client
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

    /** @var MessageFactory */
    protected $factory;

    public function __construct($appKey, $appSecret, $server)
    {

        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->server = $server;

        $this->auth = new Auth();

        $this->factory = new MessageFactory();

        parent::__construct([
            //TODO 'base_url' => $this->server,
            'message_factory' => $this->factory,
            'defaults'        => [
                'headers' => [
                    'content-type' => 'application/json',
                    'accept'       => 'application/json'
                ],
            ]
        ]);

        $this->getEmitter()->on('before', function (BeforeEvent $event) {

            $request = $event->getRequest();

            if (!isset($request->getConfig()['auth']) && $this->isAuthorized()) {
                $request->addHeader('authorization', $this->getAuthHeader());
            }

            $request->setUrl($this->apiUrl($request->getUrl(), ['addServer' => true]));

        });

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
                //print 'Refresh is required' . PHP_EOL;
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

        if (!empty($options['addServer']) && !stristr($url, 'http://') && !stristr($url, 'https://')) {
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
     * @return Response
     */
    public function authorize($username = '', $extension = '', $password = '', $remember = false)
    {

        $response = $this->authCall(self::TOKEN_ENDPOINT, [], [
            'grant_type'        => 'password',
            'username'          => $username,
            'extension'         => $extension ? $extension : null,
            'password'          => $password,
            'access_token_ttl'  => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => $remember ? self::REFRESH_TOKEN_TTL_REMEMBER : self::REFRESH_TOKEN_TTL
        ]);

        $this->auth
            ->setData($response->json(['object' => false]))
            ->setRemember($remember);

        return $response;

    }

    /**
     * @return Response
     * @throws Exception
     */
    public function refresh()
    {

        if (!$this->auth->isRefreshTokenValid()) {
            throw new Exception('Refresh token has expired');
        }

        // Synchronous
        $response = $this->authCall(self::TOKEN_ENDPOINT, [], [
            "grant_type"        => "refresh_token",
            "refresh_token"     => $this->auth->getRefreshToken(),
            "access_token_ttl"  => self::ACCESS_TOKEN_TTL,
            "refresh_token_ttl" => $this->auth->isRemember() ? self::REFRESH_TOKEN_TTL_REMEMBER : self::REFRESH_TOKEN_TTL
        ]);

        $this->auth->setData($response->json(['object' => false]));

        return $response;

    }

    /**
     * @return Response
     * @throws Exception
     */
    public function logout()
    {

        $response = $this->authCall(self::TOKEN_ENDPOINT . '/revoke', [], [
            'token' => $this->auth->getAccessToken()
        ]);

        $this->auth->reset();

        return $response;

    }

    protected function getAuthHeader()
    {
        return $this->auth->getTokenType() . ' ' . $this->auth->getAccessToken();
    }

    protected function authCall($url = '', array $queryParams = [], array $body = [])
    {

        return $this->post($url, [
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            'auth'    => [$this->appKey, $this->appSecret],
            'body'    => $body,
            'query'   => $queryParams
        ]);

    }

    /**
     * @inheritdoc
     * @param RequestInterface $request Request to send
     * @return Response
     */
    public function send(RequestInterface $request)
    {
        return parent::send($request);
    }

    /**
     * @inheritdoc
     * @return Response
     */
    public function get($url = null, $options = [])
    {
        return parent::get($url, $options);
    }

    /**
     * @inheritdoc
     * @return Response
     */
    public function post($url = null, array $options = [])
    {
        return parent::post($url, $options);
    }

    /**
     * @inheritdoc
     * @return Response
     */
    public function put($url = null, array $options = [])
    {
        return parent::put($url, $options);
    }

    /**
     * @inheritdoc
     * @return Response
     */
    public function delete($url = null, array $options = [])
    {
        return parent::delete($url, $options);
    }

}