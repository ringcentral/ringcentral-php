<?php

namespace RC\core;

use Exception;
use RC\core\ajax\Request;
use RC\core\platform\Auth;

class Platform
{

    public $server = 'https://platform.ringcentral.com';
    public $appKey = '';
    public $appSecret = '';
    public $account = '~';
    public $accountPrefix = '/account/';
    public $urlPrefix = '/restapi';
    public $tokenEndpoint = '/restapi/oauth/token';
    public $apiVersion = 'v1.0';
    public $accessTokenTtl = 600; // 10 minutes
    public $refreshTokenTtl = 36000; // 10 hours
    public $refreshTokenTtlRemember = 604800; // 1 week
    public $remember = false;
    public $cacheKey = 'platform';

    /** @var Auth */
    protected $auth = null;

    /** @var Cache */
    protected $cache = null;

    /** @var callable */
    protected $loadAuth;

    /** @var callable */
    protected $saveAuth;

    public function __construct(Cache $cache)
    {

        $this->auth = new Auth();
        $this->cache = $cache;

    }

    protected function saveAuthData($authData = null)
    {

        $this->cache->save($this->cacheKey, $this->auth->setData($authData)->getData());

        return $this;

    }

    /**
     * @return Auth
     */
    public function getAuth()
    {

        $this->auth->setData($this->cache->load($this->cacheKey));

        return $this->auth;

    }

    public function isAuthorized($refresh = true)
    {

        if (!$this->getAuth()->isAccessTokenValid()) {
            if ($refresh) {
                $this->refresh();
            }
        }

        if (!$this->getAuth()->isAccessTokenValid()) {
            throw new Exception('Access token is not valid after refresh timeout');
        }

        return $this;

    }

    protected function getApiKey()
    {
        return base64_encode($this->appKey . ':' . $this->appSecret);
    }

    protected function getAuthHeader()
    {
        $auth = $this->getAuth();
        return $auth->getTokenType() . ' ' . $auth->getAccessToken();
    }

    /**
     * @param string $url
     * @param array  $options
     * @return string
     */
    protected function apiUrl($url = '', $options = [])
    {

        $builtUrl = '';

        if ($options['addServer'] && !stristr($url, 'http://') && !stristr($url, 'https://')) {
            $builtUrl .= $this->server;
        }

        if (!stristr($url, $this->urlPrefix)) {
            $builtUrl .= $this->urlPrefix . '/' . $this->apiVersion;
        }

        if (stristr($url, $this->accountPrefix)) {
            $builtUrl = str_replace($this->accountPrefix . '~', $this->accountPrefix . $this->account, $builtUrl);
        }

        $builtUrl .= $url;

        if (!empty($options['addMethod']) || !empty($options['addToken'])) {
            $builtUrl .= (stristr($url, '?') ? '&' : '?');
        }

        if (!empty($options['addMethod'])) {
            $builtUrl .= '_method=' . $options['addMethod'];
        }
        if (!empty($options['addToken'])) {
            $builtUrl .= ($options['addMethod'] ? '&' : '') . 'access_token=' . $this->getAuth()->getAccessToken();
        }

        return $builtUrl;

    }

    /**
     * @param string $username
     * @param string $extension
     * @param string $password
     * @param bool   $remember
     * @return Ajax
     * @throws Exception
     */
    public function authorize($username = '', $extension = '', $password = '', $remember = false)
    {

        $ajax = $this->authCall(new Request(Request::POST, $this->tokenEndpoint, null, [
            'grant_type'        => 'password',
            'username'          => $username,
            'extension'         => $extension ? $extension : null,
            'password'          => $password,
            'access_token_ttl'  => $this->accessTokenTtl,
            'refresh_token_ttl' => $remember ? $this->refreshTokenTtlRemember : $this->refreshTokenTtl
        ]));

        $this->saveAuthData($ajax->getResponse()->getData());
        $this->remember = $remember;

        return $ajax;

    }

    /**
     * @return Ajax
     * @throws Exception
     */
    public function refresh()
    {

        $auth = $this->getAuth();

        if (!$auth->isPaused()) {

            print 'Refresh will be performed' . PHP_EOL;

            $auth->pause();
            $this->saveAuthData();

            if (!$auth->isRefreshTokenValid()) {
                throw new Exception('Refresh token has expired');
            }

            $ajax = $this->authCall(new Request(Request::POST, $this->tokenEndpoint, null, [
                "grant_type"        => "refresh_token",
                "refresh_token"     => $auth->getRefreshToken(),
                "access_token_ttl"  => $this->accessTokenTtl,
                "refresh_token_ttl" => $this->remember ? $this->refreshTokenTtlRemember : $this->refreshTokenTtl
            ]));

            $auth->resume();
            $this->saveAuthData($ajax->getResponse()->getData());

            return $ajax;

        } else {

            while ($auth->isPaused()) {
                print 'Waiting for refresh' . PHP_EOL;
                sleep(1);
            }

            $this->isAuthorized(false); // will throw Exception if not authorized

            return null; //TODO Recover last successful refresh

        }

    }

    /**
     * @param Request $request
     * @return Ajax
     * @throws Exception
     */
    public function apiCall(Request $request)
    {

        $this->isAuthorized();

        $request
            ->setHeader(Request::$authorizationHeader, $this->getAuthHeader())
            ->setUrl($this->apiUrl($request->getUrl(), ['addServer' => true]));

        $ajax = new Ajax($request);

        return $ajax->send();

    }

    /**
     * @param Request $request
     * @return Ajax
     * @throws Exception
     */
    public function authCall(Request $request)
    {

        $request
            ->setHeader(Request::$authorizationHeader, 'Basic ' . $this->getApiKey())
            ->setHeader(Request::$contentTypeHeader, Request::$urlEncodedContentType)
            ->setUrl($this->apiUrl($request->getUrl(), ['addServer' => true]))
            ->setMethod(Request::POST);

        $ajax = new Ajax($request);

        return $ajax->send();

    }

}