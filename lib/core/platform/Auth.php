<?php

namespace RC\core\platform;

class Auth
{

    const RELEASE_TIMEOUT = 10;

    protected static $defaultAuthData = [
        'paused_time'               => 0,
        'token_type'                => '',
        'access_token'              => '',
        'expires_in'                => 0,
        'expire_time'               => 0,
        'refresh_token'             => '',
        'refresh_token_expires_in'  => 0,
        'refresh_token_expire_time' => 0
    ];

    protected static function isTokenDateValid($tokenDate)
    {
        return $tokenDate > time();
    }

    protected $authData = [];

    public function __construct()
    {
        $this->setData(self::$defaultAuthData);
    }

    public function setData($authData = null)
    {

        $mergedData = array_merge([], self::$defaultAuthData, $this->authData, !empty($authData) ? $authData : []);

        if (empty($authData['expire_time']) && !empty($authData['expires_in'])) {
            $mergedData['expire_time'] = time() + $authData['expires_in'];
        }

        if (empty($authData['refresh_token_expire_time']) && !empty($authData['refresh_token_expires_in'])) {
            $mergedData['refresh_token_expire_time'] = time() + $authData['refresh_token_expires_in'];
        }

        $this->authData = $mergedData;

        return $this;

    }

    public function getData()
    {
        return array_merge([], $this->authData);
    }

    public function getAccessToken()
    {
        return $this->authData['access_token'];
    }

    public function getRefreshToken()
    {
        return $this->authData['refresh_token'];
    }

    public function getTokenType()
    {
        return $this->authData['token_type'];
    }

    public function isAccessTokenValid()
    {
        return self::isTokenDateValid($this->authData['expire_time']);
    }

    public function isRefreshTokenValid()
    {
        return self::isTokenDateValid($this->authData['refresh_token_expire_time']);
    }

    public function isPaused()
    {
        return $this->authData['paused_time'] > 0 && (time() - $this->authData['paused_time']) < self::RELEASE_TIMEOUT;
    }

    public function pause()
    {
        $this->authData['paused_time'] = time();
        return $this;
    }

    public function resume()
    {
        $this->authData['paused_time'] = 0;
        return $this;
    }

}