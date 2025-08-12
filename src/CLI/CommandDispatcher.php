<?php

namespace Querychan\CLI;

use Querychan\ORM\Database;
use Querychan\ORM\Migrator;
use Querychan\ORM\Utils;

class CommandDispatcher {
    public function handle(string $command, array $args = []) {
        $cmd = explode(':', $command);

        if($cmd[0] === 'create' && $cmd[1] === 'model') {
            $this->createTable($args[0]);
        }
        elseif($cmd[0] === 'create' && $cmd[1] === 'grow') {}
        elseif($cmd[0] === 'migrate' && !isset($cmd[1])) {
            $this->migrate();
        }
        elseif($cmd[0] === 'migrate' && $cmd[1] === 'refresh') {
            $this->refresh();
        }
        elseif($cmd[0] === 'migrate' && $cmd[1] === 'grow') {}
        elseif($cmd[0] === 'status' && !isset($cmd[1])) {
            $this->status();
        }
        elseif($cmd[0] === 'version' && !isset($cmd[1])) {
            $this->version();
        }
        elseif($cmd[0] === 'about' && !isset($cmd[1])) {
            $this->about();
        }
        else {
            $this->displayCommands();
        }
    }

    private function createTable($name) {
        if(empty($name)) {
            echo "Table name not found...\nUsage : qc:create TableName\n\n";
            return;
        }

        $modelName = ucfirst($name);
        $lowerModelName = strtolower($modelName);
        $modelDir = getcwd() . '/src/Models/';
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
        $modelsPath = getcwd() . '/src/Models';

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
        
        echo "(=^‥^=) Database Status\n";
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
        echo "\033[1m\033[92m(=^‥^=) Querychan version\033[0m\n";
        $version = Utils::getPackageVersion();
        if(!$version) {
            echo "\t\033[041!! Version not found !!\033[0m\n";
            return;
        } 
        
        echo "\tVersion :: $version\n";
    }
    
    private function about() {
        $version = Utils::getPackageVersion();
        echo "\033[1m\033[92m(=^‥^=) About Querychan\033[0m\n";
        echo "\tDeveloped by \033[94mLoïc Jacques - FunkyDuck.\033[0m\n\tWebsite: \033[94mhttps://funkyduck.be \033[0m\n\tVersion: \033[94m$version\033[0m\n\tMaking your DB kawaii since 2025 🍙";
    }

    public function displayCommands() {
        echo "\033[92m"; 
        echo <<<EOT
         _____                             _                                      
        /  __ \                           | |                     _____    
        | |  | |_   _  ___ _ __ _   _  ___| |__   __ _ _ __      /     \   
        | |  | | | | |/ _ \ '__| | | |/ __| '_ \ / _` | '_ \    /  ^ ^  \  
        | |__| | |_| |  __/ |  | |_| | (__| | | | (_| | | | |  |    ▬    | 
        \____\_\\\__,_|\___|_|   \__, |\___|_| |_|\__,_|_| |_|   \__▓▓▓__/  
                                 _/ /                                    
                                |__/                                     
        
        EOT;
        echo "\033[0m\n";

        echo "\t\033[41m!! COMMAND NOT FOUND !!\033[0m\n\n";
        echo "\t\033[1m\033[92m:::: Querychan CLI ::::\033[0m\n\n";
        
        echo "\t\033[1mCommands you can run :\033[0m\n";
        echo "\t\033[94mcreate:model    \033[0m► Build a new Table Model\n";
        // echo "\t\033[94mcreate:grow     \033[0m► Make a new db grower (data filler)\n";
        echo "\t\033[94mmigrate         \033[0m► Run migrations\n";
        echo "\t\033[94mmigrate:refresh \033[0m► Reset DB (DROP + CREATE + MIGRATE)\n";
        // echo "\t\033[94mmigrate:grow    \033[0m► Seed that DB (add data)\n";
        echo "\t\033[94mstatus          \033[0m► Check db status\n";
        echo "\t\033[94mabout           \033[0m► About Querychan\n";

        return;
    }
}