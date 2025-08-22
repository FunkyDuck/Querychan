<?php

namespace FunkyDuck\Querychan\Config;

use Dotenv\Dotenv;

class EnvLoader {
    public static function load() {
        $projectRoot = __DIR__ . '/../../';
        $dotenv = Dotenv::createImmutable($projectRoot);
        $dotenv->load();
    }
}