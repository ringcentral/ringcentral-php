<?php

namespace RC\core;

abstract class Cache
{

    public function __construct()
    {
    }

    public function save($key, $object)
    {
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function load($key)
    {
    }

}