<?php

namespace FunkyDuck\Querychan\ORM;

use FunkyDuck\Querychan\ORM\Database;
use FunkyDuck\NijiEcho\NijiEcho;
use PDO;

class Migrator {
    public static function runMigration(string $migrationPath): void {
        echo "\t" . NijiEcho::info(":: Start Migration ::") . "\n";

        self::ensureMigrationsTableExists();

        $pdo = Database::get();
        $ranMigration = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

        $MigrationFiles = glob($migrationPath . '/*.php');
        sort($MigrationFiles);

        $newMigrationsRun = false;

        foreach ($MigrationFiles as $file) {
            $migrationName = basename($file);

            if(!in_array($migrationName, $ranMigration)) {
                try {
                    echo "\t" . NijiEcho::text("Migrating: $migrationName")->color('light_blue') . "\n";

                    $migration = require($file);
                    $migration->up();

                    $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
                    $stmt->execute([$migrationName]);

                    $newMigrationsRun = true;
                    
                    echo NijiEcho::success("\tMigrate $migrationName successfully!") . "\n";
                } catch (\Throwable $th) {
                    echo NijiEcho::error("\t!! Fail to migrate: $migrationName") . "\n";
                    echo NijiEcho::error("\tError: " . $th->getMessage()) . "\n";
                    return;
                }
            }
        }
        echo "\t" . NijiEcho::info(":: End Migration ::") . "\n";
    }

    protected static function ensureMigrationsTableExists(): void {
        $db = Database::get();
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        $idColumn = $driver === 'sqlite'
            ? 'id INTEGER PRIMARY KEY AUTOINCREMENT'
            : 'id INT AUTO_INCREMENT PRIMARY KEY';

        $db->exec("CREATE TABLE IF NOT EXISTS migrations (
            {$idColumn},
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL DEFAULT 1
        )");
    }

    public static function dropTables(): void {
        $db = Database::get();
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        $query = $driver === 'sqlite'
            ? "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name NOT LIKE 'migrations'"
            : "SHOW TABLES";

        $tables = $db->query($query)->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $db->query("DROP TABLE IF EXISTS `$table`");
        }
    }

    public static function growTable(string $modelPath): void {
        echo NijiEcho::info(":: Start Grower ::") . "\n";
        $files = glob($modelPath . '/*.php');

        foreach ($files as $file) {
            try {
                require_once $file;

                $filename = pathinfo($file, PATHINFO_FILENAME);

                $declaredClasses = get_declared_classes();
                $classFound = null;

                foreach ($declaredClasses as $class) {
                    if (str_ends_with($class, "\\$filename") && str_contains($class, "Querychan\\Growers\\")) {
                        $classFound = $class;
                        break;
                    }
                }

                if ($classFound !== null) {
                    $model = new $classFound;
                    
                    if (method_exists($model, 'run')) {
                        $model->run();
                    }
                }
                echo NijiEcho::success("\tGrow Table : $filename") . "\n";
            } catch (\Throwable $th) {
                echo NijiEcho::error("\t!! Fail to growing Table : $filename") . "\n";
                echo NijiEcho::error("\tError: " . $th->getMessage()) . "\n";
            }
        }
        echo "\t" . NijiEcho::info(":: End Grower ::") . "\n";
    }
}