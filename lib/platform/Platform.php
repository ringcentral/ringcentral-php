<?php

namespace RingCentral\platform;

use Exception;
use RingCentral\core\Context;
use RingCentral\http\HttpException;
use RingCentral\http\Request;
use RingCentral\http\Response;

class Platform
{

    const ACCESS_TOKEN_TTL = 3600; // 60 minutes
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

    /** @var Context */
    protected $context;

    public function __construct(Context $context, $appKey, $appSecret, $server)
    {

        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->server = $server;

        $this->auth = new Auth();
        $this->context = $context;

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

        if (stristr($builtUrl, self::ACCOUNT_PREFIX)) {
            $builtUrl = str_replace(
                self::ACCOUNT_PREFIX . self::ACCOUNT_ID,
                self::ACCOUNT_PREFIX . $this->account,
                $builtUrl
            );
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

        $response = $this->authCall($this->context->getRequest(Request::POST, self::TOKEN_ENDPOINT, null, array(
            'grant_type'        => 'password',
            'username'          => $username,
            'extension'         => $extension ? $extension : null,
            'password'          => $password,
            'access_token_ttl'  => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => $remember ? self::REFRESH_TOKEN_TTL_REMEMBER : self::REFRESH_TOKEN_TTL
        )));

        $this->auth
            ->setData($response->getJson(false))
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
        $response = $this->authCall($this->context->getRequest(Request::POST, self::TOKEN_ENDPOINT, null, array(
            "grant_type"        => "refresh_token",
            "refresh_token"     => $this->auth->getRefreshToken(),
            "access_token_ttl"  => self::ACCESS_TOKEN_TTL,
            "refresh_token_ttl" => $this->auth->isRemember() ? self::REFRESH_TOKEN_TTL_REMEMBER : self::REFRESH_TOKEN_TTL
        )));

        $this->auth->setData($response->getJson(false));

        return $response;

    }

    /**
     * @return Response
     * @throws Exception
     */
    public function logout()
    {

        $response = $this->authCall($this->context->getRequest(Request::POST, self::REVOKE_ENDPOINT, array(
            'token' => $this->auth->getAccessToken()
        )));

        $this->auth->reset();

        return $response;

    }

    public function getApiKey()
    {
        return base64_encode($this->appKey . ':' . $this->appSecret);
    }

    protected function getAuthHeader()
    {
        return $this->auth->getTokenType() . ' ' . $this->auth->getAccessToken();
    }


    /**
     * @param Request $request
     * @return Response
     * @throws HttpException
     */
    protected function apiCall(Request $request)
    {

        $this->isAuthorized();

        return $request
            ->setHeader(Request::AUTHORIZATION, $this->getAuthHeader())
            ->setUrl($this->apiUrl($request->getUrl(), array('addServer' => true)))
            ->send();

    }

    /**
     * @param Request $request
     * @return Response
     * @throws HttpException
     */
    protected function authCall(Request $request)
    {

        return $request
            ->setHeader(Request::AUTHORIZATION, 'Basic ' . $this->getApiKey())
            ->setHeader(Request::CONTENT_TYPE, Request::URL_ENCODED_CONTENT_TYPE)
            ->setUrl($this->apiUrl($request->getUrl(), array('addServer' => true)))
            ->setMethod(Request::POST)
            ->send();

    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $headers
     * @return Response
     * @throws HttpException
     */
    public function get($url = '', $queryParameters = array(), array $headers = array())
    {
        return $this->apiCall($this->context->getRequest(Request::GET, $url, $queryParameters, null, $headers));
    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $body
     * @param array  $headers
     * @return Response
     * @throws HttpException
     */
    public function post($url = '', $queryParameters = array(), $body = null, array $headers = array())
    {
        return $this->apiCall($this->context->getRequest(Request::POST, $url, $queryParameters, $body, $headers));
    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $body
     * @param array  $headers
     * @return Response
     * @throws HttpException
     */
    public function put($url = '', $queryParameters = array(), $body = null, array $headers = array())
    {
        return $this->apiCall($this->context->getRequest(Request::PUT, $url, $queryParameters, $body, $headers));
    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $body
     * @param array  $headers
     * @return Response
     * @throws HttpException
     */
    public function delete($url = '', $queryParameters = array(), $body = null, array $headers = array())
    {
        return $this->apiCall($this->context->getRequest(Request::DELETE, $url, $queryParameters, $body, $headers));
    }

}