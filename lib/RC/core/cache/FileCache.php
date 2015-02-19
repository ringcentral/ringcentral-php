<?php

namespace RC\core\cache;

use RC\core\Cache;

class FileCache extends Cache
{

    protected $root = null;

    public function __construct($root)
    {
        $this->root = $root;
    }

    protected function getFile($key)
    {
        return $this->root . DIRECTORY_SEPARATOR . $key . '.json';
    }

    public function save($key, $object)
    {
        file_put_contents($this->getFile($key), json_encode($object, JSON_PRETTY_PRINT));
        return $this;
    }

    public function load($key)
    {
        $file = $this->getFile($key);
        return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
    }

}