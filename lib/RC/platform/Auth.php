<?php

namespace RC\platform;

use stdClass;

class Auth
{

    const RELEASE_TIMEOUT = 10;

    /** @var AuthData */
    protected $authData = null;

    protected $pausedTime = 0;

    protected static function isTokenDateValid($tokenDate)
    {
        return $tokenDate > time();
    }

    public function __construct()
    {
        $this->authData = new AuthData();
    }

    public function setData(stdClass $authData = null)
    {

        if (empty($authData)) {
            return $this;
        }

        // Misc

        if (!empty($authData->remember)) {
            $this->authData->remember = $authData->remember;
        }

        if (!empty($authData->token_type)) {
            $this->authData->token_type = $authData->token_type;
        }

        if (!empty($authData->owner_id)) {
            $this->authData->owner_id = $authData->owner_id;
        }

        if (!empty($authData->scope)) {
            $this->authData->scope = $authData->scope;
        }

        // Access token

        if (!empty($authData->access_token)) {
            $this->authData->access_token = $authData->access_token;
        }

        if (!empty($authData->expires_in)) {
            $this->authData->expires_in = $authData->expires_in;
        }

        if (empty($authData->expire_time) && !empty($authData->expires_in)) {
            $this->authData->expire_time = time() + $authData->expires_in;
        } elseif (!empty($authData->expire_time)) {
            $this->authData->expire_time = $authData->expire_time;
        }

        // Refresh token

        if (!empty($authData->refresh_token)) {
            $this->authData->refresh_token = $authData->refresh_token;
        }

        if (!empty($authData->refresh_token_expires_in)) {
            $this->authData->refresh_token_expires_in = $authData->refresh_token_expires_in;
        }

        if (empty($authData->refresh_token_expire_time) && !empty($authData->refresh_token_expires_in)) {
            $this->authData->refresh_token_expire_time = time() + $authData->refresh_token_expires_in;
        } elseif (!empty($authData->refresh_token_expire_time)) {
            $this->authData->refresh_token_expire_time = $authData->refresh_token_expire_time;
        }

        //print print_r($authData, true) . PHP_EOL;
        //print print_r($this->authData, true) . PHP_EOL;

        return $this;

    }

    public function reset()
    {

        $this->resume();
        $this->authData->reset();

        return $this;

    }

    public function getData()
    {
        return $this->authData;
    }

    public function getAccessToken()
    {
        return $this->authData->access_token;
    }

    public function getRefreshToken()
    {
        return $this->authData->refresh_token;
    }

    public function getTokenType()
    {
        return $this->authData->token_type;
    }

    public function isAccessTokenValid()
    {
        return self::isTokenDateValid($this->authData->expire_time);
    }

    public function isRefreshTokenValid()
    {
        return self::isTokenDateValid($this->authData->refresh_token_expire_time);
    }

    public function isPaused()
    {
        return !empty($this->pausedTime) && $this->pausedTime > 0 && (time() - $this->pausedTime) < self::RELEASE_TIMEOUT;
    }

    public function pause()
    {
        $this->pausedTime = time();
        return $this;
    }

    public function resume()
    {
        $this->pausedTime = 0;
        return $this;
    }

    public function setRemember($remember)
    {
        $this->authData->remember = !!$remember;
        return $this;
    }

    public function isRemember()
    {
        return !empty($this->authData->remember);
    }

}