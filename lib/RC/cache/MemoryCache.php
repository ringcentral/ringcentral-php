<?php

namespace RC\cache;

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