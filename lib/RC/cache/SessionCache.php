<?php

namespace RC\cache;

class SessionCache extends Cache
{

    public function save($key, $object)
    {
        $_SESSION[$key] = $object;
        return $this;
    }

    public function load($key)
    {
        return (!empty($_SESSION[$key])) ? $_SESSION[$key] : null;
    }

}