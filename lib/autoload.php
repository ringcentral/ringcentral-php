<?php

function rcsdkAutoloader($class)
{

    $prefix = 'RC\\';

    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($file)) {
        require_once($file);
    }

}

spl_autoload_register('rcsdkAutoloader', true);