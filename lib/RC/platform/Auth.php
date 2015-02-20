<?php

namespace RC\platform;

use RC\cache\Cache;

class Auth
{

    const RELEASE_TIMEOUT = 10;
    const CACHE_KEY = 'platform';

    const PAUSED_TIME = 'paused_time';
    const TOKEN_TYPE = 'token_type';
    const ACCESS_TOKEN = 'access_token';
    const EXPIRES_IN = 'expires_in';
    const EXPIRE_TIME = 'expire_time';
    const REFRESH_TOKEN = 'refresh_token';
    const REFRESH_TOKEN_EXPIRES_IN = 'refresh_token_expires_in';
    const REFRESH_TOKEN_EXPIRE_TIME = 'refresh_token_expire_time';
    const REMEMBER = 'remember';

    protected static $defaultAuthData = [
        self::PAUSED_TIME               => 0,
        self::TOKEN_TYPE                => '',
        self::ACCESS_TOKEN              => '',
        self::EXPIRES_IN                => 0,
        self::EXPIRE_TIME               => 0,
        self::REFRESH_TOKEN             => '',
        self::REFRESH_TOKEN_EXPIRES_IN  => 0,
        self::REFRESH_TOKEN_EXPIRE_TIME => 0,
        self::REMEMBER                  => false,
    ];

    protected static function isTokenDateValid($tokenDate)
    {
        return $tokenDate > time();
    }

    protected $cache = [];

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function setData($authData = null)
    {

        $mergedData = array_merge([], self::$defaultAuthData, $this->getData(), !empty($authData) ? $authData : []);

        if (empty($authData[self::EXPIRE_TIME]) && !empty($authData[self::EXPIRES_IN])) {
            $mergedData[self::EXPIRE_TIME] = time() + $authData[self::EXPIRES_IN];
        }

        if (empty($authData[self::REFRESH_TOKEN_EXPIRE_TIME]) && !empty($authData[self::REFRESH_TOKEN_EXPIRES_IN])) {
            $mergedData[self::REFRESH_TOKEN_EXPIRE_TIME] = time() + $authData[self::REFRESH_TOKEN_EXPIRES_IN];
        }

        $this->cache->save(self::CACHE_KEY, $mergedData);

        return $this;

    }

    public function getData()
    {
        $cached = $this->cache->load(self::CACHE_KEY);
        return $cached ? $cached : [];
    }

    public function getAccessToken()
    {
        return $this->getData()[self::ACCESS_TOKEN];
    }

    public function getRefreshToken()
    {
        return $this->getData()[self::REFRESH_TOKEN];
    }

    public function getTokenType()
    {
        return $this->getData()[self::TOKEN_TYPE];
    }

    public function isAccessTokenValid()
    {
        return self::isTokenDateValid($this->getData()[self::EXPIRE_TIME]);
    }

    public function isRefreshTokenValid()
    {
        return self::isTokenDateValid($this->getData()[self::REFRESH_TOKEN_EXPIRE_TIME]);
    }

    public function isPaused()
    {
        $data = $this->getData();
        return !empty($data[self::PAUSED_TIME]) && $data[self::PAUSED_TIME] > 0 && (time() - $data[self::PAUSED_TIME]) < self::RELEASE_TIMEOUT;
    }

    public function pause()
    {
        $this->setData([self::PAUSED_TIME => time()]);
        return $this;
    }

    public function resume()
    {
        $this->setData([self::PAUSED_TIME => 0]);
        return $this;
    }

    public function setRemember($remember)
    {
        $this->setData([self::REMEMBER => !!$remember]);
        return $this;
    }

    public function isRemember()
    {
        return !empty($this->getData()[self::REMEMBER]);
    }

}