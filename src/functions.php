<?php

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Container\Container;

function base_path($path = null)
{
    $container = Container::getInstance();
    return $container->get('config')['basePath'].($path ? DIRECTORY_SEPARATOR.$path : $path);
}
