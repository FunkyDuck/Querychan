<?php

namespace FunkyDuck\Querychan\ORM;

use FunkyDuck\Querychan\ORM\Database;
use FunkyDuck\NijiEcho\NijiEcho;
use PDO;

class Migrator {
    public static function migrateModels(string $modelPath): void {
        echo NijiEcho::info(":: Start Migration ::") . "\n";
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
                echo NijiEcho::success("\tMigrate Table : $className") . "\n";
            } catch (\Throwable $th) {
                echo NijiEcho::error("\t!! Fail to migrate Table : $className") . "\n";
                echo NijiEcho::error("\tError: " . $th->getMessage()) . "\n";
            }
        }
        echo NijiEcho::info(":: End Migration ::") . "\n\n";
    }

    public static function dropTables(): void {
        $db = Database::get();
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

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
        echo NijiEcho::info(":: End Grower ::") . "\n\n";
    }
}