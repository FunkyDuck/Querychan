<?php

namespace Querychan\Config;

use Dotenv\Dotenv;

class EnvLoader {
    public static function load(string $path = __DIR__ . '/../../') {
        $dotenv = Dotenv::createImmutable($path);
        $dotenv->load();
    }
}