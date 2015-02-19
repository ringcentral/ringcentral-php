<?php

namespace RC\core\cache;

use RC\core\Cache;

class MemoryCache extends Cache
{

    protected $store = [];

    public function save($key, $object)
    {
        $this->store[$key] = $object;
        return $this;
    }

    public function load($key)
    {
        //TODO Safe load vs. throw Exception
        return (!empty($this->store[$key])) ? $this->store[$key] : null;
    }

}