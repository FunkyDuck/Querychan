<?php

require_once __DIR__ . '/vendor/autoload.php';

use Querychan\Config\EnvLoader;

$dsn = $_ENV['DB_DRIVER'] . ':host=' .$_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

$pdo = new PDO($dsn, $user, $password);