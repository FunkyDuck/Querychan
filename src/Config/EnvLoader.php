<?php

namespace Querychan\Config;

use Dotenv\Dotenv;

class EnvLoader {
    public static function load() {
        $path = __DIR__ . '/../../';
        if(!file_exists($path)) {
            $path = '/../../../../../';
        }
        $dotenv = Dotenv::createImmutable($path);
        $dotenv->load();
    }
}