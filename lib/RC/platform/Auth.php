<?php

namespace RC\platform;

use stdClass;

class Auth
{

    const RELEASE_TIMEOUT = 10;

    protected $pausedTime;

    protected $remember;

    protected $token_type;

    protected $access_token;
    protected $expires_in;
    protected $expire_time;

    protected $refresh_token;
    protected $refresh_token_expires_in;
    protected $refresh_token_expire_time;

    protected $scope;
    protected $owner_id;

    protected static function isTokenDateValid($tokenDate)
    {
        return $tokenDate > time();
    }

    public function __construct()
    {
        $this->reset();
    }

    public function setData(stdClass $data = null)
    {

        if (empty($data)) {
            return $this;
        }

        // Misc

        if (!empty($data->remember)) {
            $this->remember = $data->remember;
        }

        if (!empty($data->token_type)) {
            $this->token_type = $data->token_type;
        }

        if (!empty($data->owner_id)) {
            $this->owner_id = $data->owner_id;
        }

        if (!empty($data->scope)) {
            $this->scope = $data->scope;
        }

        // Access token

        if (!empty($data->access_token)) {
            $this->access_token = $data->access_token;
        }

        if (!empty($data->expires_in)) {
            $this->expires_in = $data->expires_in;
        }

        if (empty($data->expire_time) && !empty($data->expires_in)) {
            $this->expire_time = time() + $data->expires_in;
        } elseif (!empty($data->expire_time)) {
            $this->expire_time = $data->expire_time;
        }

        // Refresh token

        if (!empty($data->refresh_token)) {
            $this->refresh_token = $data->refresh_token;
        }

        if (!empty($data->refresh_token_expires_in)) {
            $this->refresh_token_expires_in = $data->refresh_token_expires_in;
        }

        if (empty($data->refresh_token_expire_time) && !empty($data->refresh_token_expires_in)) {
            $this->refresh_token_expire_time = time() + $data->refresh_token_expires_in;
        } elseif (!empty($data->refresh_token_expire_time)) {
            $this->refresh_token_expire_time = $data->refresh_token_expire_time;
        }

        //print print_r($authData, true) . PHP_EOL;
        //print print_r($this->getData(), true) . PHP_EOL;

        return $this;

    }

    public function reset()
    {

        $this->resume();

        $this->remember = false;

        $this->token_type = '';

        $this->access_token = '';
        $this->expires_in = 0;
        $this->expire_time = 0;

        $this->refresh_token = '';
        $this->refresh_token_expires_in = 0;
        $this->refresh_token_expire_time = 0;

        $this->scope = '';
        $this->owner_id = '';

        return $this;

    }

    /**
     * @return stdClass
     */
    public function getData()
    {

        $data = new stdClass();

        $data->remember = $this->remember;

        $data->token_type = $this->token_type;

        $data->access_token = $this->access_token;
        $data->expires_in = $this->expires_in;
        $data->expire_time = $this->expire_time;

        $data->refresh_token = $this->refresh_token;
        $data->refresh_token_expires_in = $this->refresh_token_expires_in;
        $data->refresh_token_expire_time = $this->refresh_token_expire_time;

        $data->scope = $this->scope;
        $data->owner_id = $this->owner_id;

        return $data;

    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    public function getTokenType()
    {
        return $this->token_type;
    }

    public function isAccessTokenValid()
    {
        return self::isTokenDateValid($this->expire_time);
    }

    public function isRefreshTokenValid()
    {
        return self::isTokenDateValid($this->refresh_token_expire_time);
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
        $this->remember = !!$remember;
        return $this;
    }

    public function isRemember()
    {
        return !empty($this->remember);
    }

}