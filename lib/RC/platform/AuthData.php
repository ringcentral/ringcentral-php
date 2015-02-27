<?php

namespace RC\platform;

use stdClass;

class AuthData extends stdClass
{

    public $remember = false;

    public $token_type = '';

    public $access_token = '';
    public $expires_in = 0;
    public $expire_time = 0;

    public $refresh_token = '';
    public $refresh_token_expires_in = 0;
    public $refresh_token_expire_time = 0;

    public $scope = '';
    public $owner_id = '';

    public function __construct()
    {
    }

    public function reset()
    {

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

}