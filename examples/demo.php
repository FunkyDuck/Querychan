<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FunkyDuck\Querychan\ORM\Database;
use FunkyDuck\Querychan\ORM\Model;
use FunkyDuck\Querychan\Config\EnvLoader;


// Load ENV var
EnvLoader::load(__DIR__ . '/../');

// Connect to the database
$dsn = $_ENV['DB_DRIVER'] . ':host=' . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'] . ';dbname=' . $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];
Database::connect($dsn, $user, $password);