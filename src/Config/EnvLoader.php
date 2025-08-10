<?php

namespace Querychan\Config;

use Dotenv\Dotenv;

class EnvLoader {
    public static function load() {
        $path = getcwd() . DIRECTORY_SEPARATOR . '.env';
        $dotenv = Dotenv::createImmutable($path);
        $dotenv->load();
    }
}