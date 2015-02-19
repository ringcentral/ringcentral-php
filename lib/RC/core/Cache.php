<?php

namespace RC\core;

abstract class Cache
{

    /**
     * @param string $key
     * @param mixed  $object
     * @return $this
     */
    abstract public function save($key, $object);

    /**
     * @param $key
     * @return mixed
     */
    abstract public function load($key);

}