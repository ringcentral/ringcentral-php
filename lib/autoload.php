<?php

function RingCentralAutoLoader($class)
{

    $prefix = 'RingCentral\\';

    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $file = __DIR__ . $class . '.php';
    $file = str_replace($prefix, DIRECTORY_SEPARATOR, $file);
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

    //print 'RC Autoload: ' . $file . PHP_EOL;

    if (file_exists($file)) {
        require_once($file);
    }

}

spl_autoload_register('RingCentralAutoLoader', true);