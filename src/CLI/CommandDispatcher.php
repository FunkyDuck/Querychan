<?php

namespace Querychan\CLI;

use Querychan\ORM\Database;
use Querychan\ORM\Migrator;
use Querychan\ORM\Utils;

class CommandDispatcher {
    public function handle(string $command, array $args = []) {
        $cmd = explode(':', $command);
        $cmd[1] = ($cmd[0] === 'qc') ? $cmd[1] : null;
        switch($cmd[1]) {
            case 'create': 
                $this->createTable($args[0]);
                break;
            
            case 'migrate':
                $this->migrate();
                break;
            
            case 'refresh':
                $this->refresh();
                break;

            case 'status':
                $this->status();
                break;

            case 'version':
                $this->version();
                break;
            
            default:
                echo "!! Unknow command !!\n\tqc:create -> Create a Table Model\n\tqc:migrate -> Migrate data\n\tqc:refresh -> Reset database (DROP + CREATE + MIGRATE)\n\tqc:status -> View database status\n\n";
                break;
        }
    }

    private function createTable($name) {
        if(empty($name)) {
            echo "Table name not found...\nUsage : qc:create TableName\n\n";
            return;
        }

        $modelName = ucfirst($name);
        $lowerModelName = strtolower($modelName);
        $modelDir = __DIR__ . '/../../src/Models/';
        $stubPath = __DIR__ . '/../../src/ORM/Stubs/Model.stub';
        $timestamp = time();
        $filepath = $modelDir . $timestamp . '-' . $modelName . '.php';

        if(file_exists($filepath)) {
            echo "!! A model have already this name : $timestamp-$modelName.\n\n";
            return;
        }
        
        if(!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }

        if(!file_exists($stubPath)) {
            echo("!! Stub not found.\n\n");
            return;
        }

        $template = file_get_contents($stubPath);
        $content = str_replace(
            ['{{$modelName}}', '{{$lowerModelName}}'],
            [$modelName, $lowerModelName],
            $template
        );

        file_put_contents($filepath, $content);
        echo "Table Model [$modelName] created.\n\n";
    }

    private function migrate() {
        $modelsPath = __DIR__ . '/../../src/Models';

        if(!is_dir($modelsPath)) {
            echo "!! No models directory found.\n";
            return;
        }

        Migrator::migrateModels($modelsPath);
    }

    private function refresh() {
        echo "Refresh started\n";
        Migrator::dropTables();
        echo "Drop tables :: OK\n";
        self::migrate();
    }

    private function status() {
        Database::connect();
        $info = Database::status();
        
        echo "Database Status\n";
        echo "\tConnection :: " . ($info['connected'] ? "OK" : "X") . "\n";
        echo "\tVersion :: {$info['version']}\n";
        echo "\tTables ::\n";
        if(count($info['tables']) === 0) {
            echo "\t !! Tables not found !!\t";
        }
        else {
            foreach($info['tables'] as $tab) {
                echo "\t  >> $tab\n";
            }
        }
    }

    private function version() {
        echo "Querychan version\n";
        $version = Utils::getPackageVersion();
        if(!$version) {
            echo "\t!! Version not found !!\n";
            return;
        } 

        echo "\tVersion :: $version\n";
    }
}