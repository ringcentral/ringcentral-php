<?php

namespace RingCentral\SDK\Platform;

use stdClass;

class Auth
{

    protected $token_type;

    protected $access_token;
    protected $expires_in;
    protected $expire_time;

    protected $refresh_token;
    protected $refresh_token_expires_in;
    protected $refresh_token_expire_time;

    protected $scope;
    protected $owner_id;

    public function __construct()
    {
        $this->reset();
    }

    public function setData(array $data = array())
    {

        if (empty($data)) {
            return $this;
        }

        // Misc

        if (!empty($data['token_type'])) {
            $this->token_type = $data['token_type'];
        }

        if (!empty($data['owner_id'])) {
            $this->owner_id = $data['owner_id'];
        }

        if (!empty($data['scope'])) {
            $this->scope = $data['scope'];
        }

        // Access token

        if (!empty($data['access_token'])) {
            $this->access_token = $data['access_token'];
        }

        if (!empty($data['expires_in'])) {
            $this->expires_in = $data['expires_in'];
        }

        if (empty($data['expire_time']) && !empty($data['expires_in'])) {
            $this->expire_time = time() + $data['expires_in'];
        } elseif (!empty($data['expire_time'])) {
            $this->expire_time = $data['expire_time'];
        }

        // Refresh token

        if (!empty($data['refresh_token'])) {
            $this->refresh_token = $data['refresh_token'];
        }

        if (!empty($data['refresh_token_expires_in'])) {
            $this->refresh_token_expires_in = $data['refresh_token_expires_in'];
        }

        if (empty($data['refresh_token_expire_time']) && !empty($data['refresh_token_expires_in'])) {
            $this->refresh_token_expire_time = time() + $data['refresh_token_expires_in'];
        } elseif (!empty($data['refresh_token_expire_time'])) {
            $this->refresh_token_expire_time = $data['refresh_token_expire_time'];
        }

        return $this;

    }

    public function reset()
    {

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
    public function data()
    {

        return array(
            'token_type'                => $this->token_type,
            'access_token'              => $this->access_token,
            'expires_in'                => $this->expires_in,
            'expire_time'               => $this->expire_time,
            'refresh_token'             => $this->refresh_token,
            'refresh_token_expires_in'  => $this->refresh_token_expires_in,
            'refresh_token_expire_time' => $this->refresh_token_expire_time,
            'scope'                     => $this->scope,
            'owner_id'                  => $this->owner_id,
        );

    }

    public function accessToken()
    {
        return $this->access_token;
    }

    public function refreshToken()
    {
        return $this->refresh_token;
    }

    public function tokenType()
    {
        return $this->token_type;
    }

    public function accessTokenValid()
    {
        return $this->expire_time > time();
    }

    public function refreshTokenValid()
    {
        return $this->refresh_token_expire_time > time();
    }

}