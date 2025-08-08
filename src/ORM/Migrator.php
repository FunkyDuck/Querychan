<?php

namespace Querychan\ORM;

use Querychan\ORM\Database;
use PDO;

class Migrator {
    public static function migrateModels(string $modelPath): void {
        echo ":: Start Migration ::\n";
        $files = glob($modelPath . '/*.php');

        foreach ($files as $file) {
            try {
                require_once $file;

                $filename = pathinfo($file, PATHINFO_FILENAME);
                $parts = explode('-', $filename, 2);
                $className = count($parts) === 2 ? $parts[1] : $filename;

                $fqcn = "Querychan\\Models\\$className";

                if (class_exists($fqcn)) {
                    $model = new $fqcn();

                    if (method_exists($fqcn, 'migrate')) {
                        $model->migrate();
                    }
                }
                echo "\tMigrate Table : $className\n";
            } catch (\Throwable $th) {
                echo "\t!! Fail to migrate Table : $className\n";
                echo "\tError: " . $th->getMessage() . "\n";
            }
        }
        echo ":: End Migration ::\n\n";
    }

    public static function dropTables(): void {
        $db = Database::get();
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $db->query("DROP TABLE IF EXISTS `$table`");
        }
    }
}