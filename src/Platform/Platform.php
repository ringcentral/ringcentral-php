<?php

namespace RingCentral\SDK\Platform;

use Exception;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use RingCentral\SDK\Http\Client;
use RingCentral\SDK\Http\Transaction;

class Platform
{

    const ACCESS_TOKEN_TTL = 3600; // 60 minutes
    const REFRESH_TOKEN_TTL = 36000; // 10 hours
    const REFRESH_TOKEN_TTL_REMEMBER = 604800; // 1 week
    const TOKEN_ENDPOINT = '/restapi/oauth/token';
    const REVOKE_ENDPOINT = '/restapi/oauth/revoke';
    const API_VERSION = 'v1.0';
    const URL_PREFIX = '/restapi';

    protected $server;
    protected $appKey;
    protected $appSecret;

    /** @var Auth */
    protected $auth;

    /** @var Client */
    protected $client;

    public function __construct(Client $client, $appKey, $appSecret, $server)
    {

        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->server = $server;

        $this->auth = new Auth();
        $this->client = $client;

    }

    /**
     * @param array $authData
     * @return $this
     */
    public function setAuthData(array $authData = array())
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

    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function setAppCredentials($appKey, $appSecret)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        return $this;
    }

    public function getAppCredentials()
    {
        return array(
            'appKey'    => $this->appKey,
            'appSecret' => $this->appSecret,
        );
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
    public function apiUrl($url = '', $options = array())
    {

        $builtUrl = '';
        $hasHttp = stristr($url, 'http://') || stristr($url, 'https://');

        if (!empty($options['addServer']) && !$hasHttp) {
            $builtUrl .= $this->server;
        }

        if (!stristr($url, self::URL_PREFIX) && !$hasHttp) {
            $builtUrl .= self::URL_PREFIX . '/' . self::API_VERSION;
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
     * @return Transaction
     */
    public function authorize($username = '', $extension = '', $password = '', $remember = false)
    {

        $response = $this->authCall(self::TOKEN_ENDPOINT, array(
            'grant_type'        => 'password',
            'username'          => $username,
            'extension'         => $extension ? $extension : null,
            'password'          => $password,
            'access_token_ttl'  => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => $remember ? self::REFRESH_TOKEN_TTL_REMEMBER : self::REFRESH_TOKEN_TTL
        ));

        $this->auth
            ->setData($response->getJson(false))
            ->setRemember($remember);

        return $response;

    }

    /**
     * @return Transaction
     * @throws Exception
     */
    public function refresh()
    {

        if (!$this->auth->isRefreshTokenValid()) {
            throw new Exception('Refresh token has expired');
        }

        // Synchronous
        $response = $this->authCall(self::TOKEN_ENDPOINT, array(
            "grant_type"        => "refresh_token",
            "refresh_token"     => $this->auth->getRefreshToken(),
            "access_token_ttl"  => self::ACCESS_TOKEN_TTL,
            "refresh_token_ttl" => $this->auth->isRemember() ? self::REFRESH_TOKEN_TTL_REMEMBER : self::REFRESH_TOKEN_TTL
        ));

        $this->auth->setData($response->getJson(false));

        return $response;

    }

    /**
     * @return Transaction
     * @throws Exception
     */
    public function logout()
    {

        $response = $this->authCall(self::REVOKE_ENDPOINT, array(
            'token' => $this->auth->getAccessToken()
        ));

        $this->auth->reset();

        return $response;

    }

    public function getApiKey()
    {
        return base64_encode($this->appKey . ':' . $this->appSecret);
    }

    public function getAuthHeader()
    {
        return $this->auth->getTokenType() . ' ' . $this->auth->getAccessToken();
    }

    /**
     * Convenience helper used for processing requests (even externally created)
     * Performs access token refresh if needed
     * Then adds Authorization header and API server to URI
     * @param RequestInterface $request
     * @param boolean          $ensureAuthorization
     * @return RequestInterface
     */
    public function processRequest(RequestInterface $request, $ensureAuthorization = true)
    {

        if ($ensureAuthorization) {

            $this->isAuthorized();

            $request = $request->withHeader('Authorization', $this->getAuthHeader());

        }

        $uri = new Uri($this->apiUrl((string)$request->getUri(), array('addServer' => true)));

        return $request->withUri($uri);

    }

    /**
     * Method sends the request (even externally created) to API server using client
     * @param RequestInterface $request
     * @param boolean          $ensureAuthorization
     * @return Transaction
     */
    public function apiCall(RequestInterface $request, $ensureAuthorization = true)
    {

        return $this->client->send($this->processRequest($request, $ensureAuthorization));

    }

    /**
     * @param string $url
     * @param array  $body
     * @return Transaction
     */
    protected function authCall($url = '', $body = array())
    {

        $headers = array(
            'Authorization' => 'Basic ' . $this->getApiKey(),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        );

        $request = $this->client->requestFactory('POST', $url, null, $body, $headers);

        $uri = new Uri($this->apiUrl((string)$request->getUri(), array('addServer' => true)));

        return $this->client->send($request->withUri($uri));

    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $headers
     * @return Transaction
     */
    public function get($url = '', $queryParameters = array(), array $headers = array())
    {
        return $this->apiCall($this->client->requestFactory('GET', $url, $queryParameters, null, $headers));
    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $body
     * @param array  $headers
     * @return Transaction
     */
    public function post($url = '', $queryParameters = array(), $body = null, array $headers = array())
    {
        return $this->apiCall($this->client->requestFactory('POST', $url, $queryParameters, $body, $headers));
    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $body
     * @param array  $headers
     * @return Transaction
     */
    public function put($url = '', $queryParameters = array(), $body = null, array $headers = array())
    {
        return $this->apiCall($this->client->requestFactory('PUT', $url, $queryParameters, $body, $headers));
    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $body
     * @param array  $headers
     * @return Transaction
     */
    public function delete($url = '', $queryParameters = array(), $body = null, array $headers = array())
    {
        return $this->apiCall($this->client->requestFactory('DELETE', $url, $queryParameters, $body, $headers));
    }

}