<?php

namespace FunkyDuck\Querychan\ORM;

use PDO;
use FunkyDuck\Querychan\Config\EnvLoader;

class Database {
    protected static ?PDO $connection = null;

    public static function connect(): void {
        EnvLoader::load();

        $driver   = getenv('DB_DRIVER') ?: 'mysql';
        $host     = getenv('DB_HOST') ?: '127.0.0.1';
        $port     = getenv('DB_PORT') ?: '3306';
        $dbname   = getenv('DB_DATABASE') ?: 'querychan_db';
        $user     = getenv('DB_USERNAME') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: 'root';
        $charset  = 'utf8mb4';
        
        $dsn = "$driver:host=$host;port=$port;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        try {
            self::$connection = new PDO($dsn, $user, $password, $options);
        } catch (\Throwable $th) {
            echo "!! DB :: Not Initialized : " . $th->getMessage() . ".\n";
            exit(1);
        }
    }

    public static function get(): PDO {
        if(!self::$connection) {
            echo "!! DB :: Not Initialized.\n";
            throw new \RuntimeException("Database connection not initialized.");
        }
        return self::$connection;
    }

    public static function status(): array {
        $pdo = self::get();

        $version = $pdo->query("SELECT VERSION()")->fetchColumn();

        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        return [
            'connected' => true,
            'version' => $version,
            'tables' => $tables
        ];
    }
}