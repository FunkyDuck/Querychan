<?php

namespace Querychan\Config;

use Dotenv\Dotenv;

class EnvLoader {
    public static function load() {
        $dotenv = Dotenv::createImmutable(getcwd());
        $dotenv->load();
    }
}