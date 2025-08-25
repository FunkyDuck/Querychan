<?php

namespace FunkyDuck\Querychan\ORM;

use FunkyDuck\NijiEcho\NijiEcho;
use PDO;

class Database {
    protected static ?PDO $connection = null;

    public static function connect(?array $config = null): void {
        if($config) {
            $driver   = $config['driver'];
            $dbname   = $config['database'];
            $user     = $config['user'] ?? null;
            $password = $config['password'] ?? null;
        } 
        else {
            $driver   = getenv('DB_DRIVER') ?: 'mysql';
            $dbname   = getenv('DB_DATABASE') ?: 'querychan_db';
            $user     = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: 'root';
        }
        $host     = getenv('DB_HOST') ?: '127.0.0.1';
        $port     = getenv('DB_PORT') ?: '3306';
        $charset  = 'utf8mb4';
        
        if($driver === 'sqlite') {
            $dsn = "sqlite:$dbname";
            $user = null;
            $password = null;
        }
        else {
            $dsn = "$driver:host=$host;port=$port;dbname=$dbname;charset=$charset";
        }
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        try {
            self::$connection = new PDO($dsn, $user, $password, $options);
        } catch (\Throwable $th) {
            echo NijiEcho::error("!! DB :: Not Initialized : " . $th->getMessage()) . "\n";
            exit(1);
        }
    }

    public static function get(): PDO {
        if(!self::$connection) {
            self::connect();
        }
        return self::$connection;
    }

    public static function disconnect(): void {
        self::$connection = null;
    }

    public static function status(): array {
        $pdo = self::get();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $version = $pdo->query("SELECT " . ($driver === 'sqlite' ? "sqlite_version()" : "VERSION()"))->fetchColumn();
        
        $query = $driver === 'sqlite'
            ? "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
            : "SHOW TABLES";

        $tables = $pdo->query($query)->fetchAll(PDO::FETCH_COLUMN);

        return [
            'connected' => true,
            'driver' => $driver,
            'version' => $version,
            'tables' => $tables
        ];
    }

    public static function dropTableIfExists(string $table): void {
        echo "\t" . NijiEcho::info("Try to remove `$table` table...") . "\n";

        $sql = "DROP TABLE IF EXISTS `$table`;";

        try {
            $pdo = self::get();
            $pdo->exec($sql);

            echo "\t" . NijiEcho::success("Table `$table` dropped successfully.") . "\n";
        } catch (\Throwable $th) {
            echo "\t" . NijiEcho::error("ERROR WITH DROP TABLE `$table` !") . "\n";
        }
    }
}