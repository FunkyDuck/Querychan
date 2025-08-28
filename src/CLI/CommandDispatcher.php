<?php

namespace FunkyDuck\Querychan\CLI;

use FunkyDuck\Querychan\ORM\Database;
use FunkyDuck\Querychan\ORM\Migrator;
use FunkyDuck\Querychan\ORM\Utils;
use FunkyDuck\NijiEcho\NijiEcho;

class CommandDispatcher {
    public function handle(string $command, array $args = []) {
        $cmd = explode(':', $command);

        if($cmd[0] === 'create' && $cmd[1] === 'model') {
            $this->createModel($args[0] ?? null);
        }
        elseif($cmd[0] === 'create' && $cmd[1] === 'migration') {
            $this->createMigration($args[0] ?? null);
        }
        elseif($cmd[0] === 'create' && $cmd[1] === 'grower') {
            $this->createGrower($args[0] ?? null);
        }
        elseif($cmd[0] === 'migrate' && !isset($cmd[1])) {
            $this->migrate();
        }
        elseif($cmd[0] === 'migrate' && $cmd[1] === 'refresh') {
            $this->refresh();
        }
        elseif($cmd[0] === 'migrate' && $cmd[1] === 'grower') {
            $this->grow();
        }
        elseif($cmd[0] === 'status' && !isset($cmd[1])) {
            $this->status();
        }
        elseif(($cmd[0] === 'about' || $cmd[0] === 'version') && !isset($cmd[1])) {
            $this->about();
        }
        elseif($cmd[0] === "help") {
            $this->displayCommands(false);
        }
        else {
            $this->displayCommands();
        }
    }

    private function createModel($name) {
        if(empty($name)) {
            echo "\t" . NijiEcho::warning("Model name not found...") . "\n\t" . NijiEcho::info("Usage : create:model ModelName") . "\n\n";
            return;
        }

        $modelName = Utils::toTitleCase($name);
        $lowerModelName = Utils::titleToSnake($modelName);
        $modelDir = getcwd() . '/src/Models/';
        $stubPath = __DIR__ . '/../../src/ORM/Stubs/Model.stub';
        $filepath = $modelDir . $modelName . '.php';

        if(file_exists($filepath)) {
            echo "\t" . NijiEcho::warning("!! A model have already this name : $modelName") . "\n\n";
            return;
        }
        
        if(!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }
        
        if(!file_exists($stubPath)) {
            echo "\t" . NijiEcho::error("!! Model.stub not found") . "\n\n";
            return;
        }
        
        $template = file_get_contents($stubPath);
        $content = str_replace(
            ['{{$modelName}}', '{{$lowerModelName}}'],
            [$modelName, $lowerModelName],
            $template
        );
        
        file_put_contents($filepath, $content);
        echo "\t" . NijiEcho::success("Model [$modelName] created") . "\n";
        $migrationName = "create_{$lowerModelName}_table";
        $this->createMigration($migrationName);
    }
    
    private function createMigration($name) {
        if(empty($name)) {
            echo "\t" . NijiEcho::warning("Migration name not found...") . "\n\t" . NijiEcho::info("Usage : create:migration MigrationName") . "\n\n";
            return;
        }
        // $modelName = Utils::toTitleCase($name);
        // $lowerModelName = Utils::titleToSnake($modelName);
        $modelDir = getcwd() . '/database/migrations/';
        $stubPath = __DIR__ . '/../../src/ORM/Stubs/Migration.stub';
        $filepath = $modelDir . time() . '_' . $name . '.php';
        
        if(file_exists($filepath)) {
            echo "\t" . NijiEcho::warning("!! A migration have already this name : $name") . "\n\n";
            return;
        }
        
        if(!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }
        
        if(!file_exists($stubPath)) {
            echo "\t" . NijiEcho::error("!! Migration.stub not found") . "\n\n";
            return;
        }

        $tableName = 'default_table';
        // Match with create migrations
        if(preg_match('/^create_(.*)_table$/', $name, $matches)) {
            $tableName = $matches[1];
        }
        // TODO :: Others migrations type
        
        $template = file_get_contents($stubPath);
        $content = str_replace('{{$tableName}}', $tableName, $template);
        
        file_put_contents($filepath, $content);
        echo "\t" . NijiEcho::success("Migration [$name] created") . "\n";
    }
    
    private function createGrower($name) {
        if(empty($name)) {
            echo "\t" . NijiEcho::warning("Grower name not found...") . "\n\t" . NijiEcho::info("Usage : create:grower GrowerName") . "\n\n";
            return;
        }
        
        $modelName = Utils::toTitleCase($name);
        $lowerModelName = Utils::titleToSnake($modelName);
        $modelDir = getcwd() . '/src/Growers/';
        $stubPath = __DIR__ . '/../../src/ORM/Stubs/Grower.stub';
        $filepath = $modelDir . $modelName . '.php';
        
        if(file_exists($filepath)) {
            echo "\t" . NijiEcho::warning("!! A grower have already this name : $modelName") . "\n\n";
            return;
        }
        
        if(!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }
        
        if(!file_exists($stubPath)) {
            echo "\t" . NijiEcho::error("!! Grower.stub not found") . "\n\n";
            return;
        }
        
        $template = file_get_contents($stubPath);
        $content = str_replace(
            ['{{$modelName}}', '{{$lowerModelName}}'],
            [$modelName, $lowerModelName],
            $template
        );
        
        file_put_contents($filepath, $content);
        echo "\t" . NijiEcho::success("Table Grower [$modelName] created") . "\n\n";
    }

    private function migrate() {
        $migrationsPath = getcwd() . '/database/migrations';

        if(!is_dir($migrationsPath)) {
            echo "!! No models directory found.\n";
            return;
        }

        Migrator::runMigration($migrationsPath);
    }

    private function refresh() {
        echo "Refresh started\n";
        Migrator::dropTables();
        echo "Drop tables :: OK\n";
        self::migrate();
    }

    private function grow() {
        $modelsPath = getcwd() . '/src/Growers';

        if(!is_dir($modelsPath)) {
            echo "!! No models directory found.\n";
            return;
        }

        Migrator::growTable($modelsPath);
    }

    private function status() {
        $this->getLogo();
        
        Database::connect();
        $info = Database::status();
        
        echo "\t" . NijiEcho::text("(=^‥^=) Database Status")->color('light_green') . "\n";
        echo "\t" . NijiEcho::text("Connection :: ") . NijiEcho::text(($info['connected'] ? " OK " : " X "))->background($info['connected'] ? 'green' : 'red') . "\n";
        echo "\t" . NijiEcho::text("Driver :: {$info['driver']}") . "\n";
        echo "\t" . NijiEcho::text("Version :: {$info['version']}") . "\n";
        echo "\t" . NijiEcho::text("Tables ::") . "\n";
        if(count($info['tables']) === 0) {
            echo "\t\t" . NijiEcho::text("!! Tables not found !!")->color("red") . "\n";
        }
        else {
            foreach($info['tables'] as $tab) {
                echo "\t\t" . NijiEcho::text("🗂️   $tab") . "\n";
            }
        }
    }
    
    private function about() {
        $this->getLogo();

        $version = Utils::getPackageVersion() ?? 'DEV';
        echo "\t" . NijiEcho::text("(=^‥^=) About Querychan")->color('light_green') . "\n";
        echo "\t" . NijiEcho::text("Developed by ") . NijiEcho::text("Loïc Jacques - FunkyDuck.")->color('light_blue') . "\n";
        echo "\t" . NijiEcho::text("Website: ") . NijiEcho::text("https://funkyduck.be")->color('light_blue') . "\n";
        echo "\t" . NijiEcho::text("Version: ") . NijiEcho::text($version)->color('light_blue') . "\n";
        echo "\t" . NijiEcho::text("Making your DB kawaii since 2025 🍙")->color('cyan') . "\n";
    }

    public function displayCommands(bool $badCommand = true) {
        $this->getLogo();
        
        if($badCommand) {
            echo "\t" . NijiEcho::text("!! COMMAND NOT FOUND !!")->color('white')->background('red') . "\n\n";
        }
        echo "\t" . NijiEcho::text(":::: Querychan CLI ::::")->color("green") . "\n\n";
        
        echo "\t" . NijiEcho::text("Commands you can run :")->color('white') . "\n";
        echo "\t" . NijiEcho::text("create:model")->color("light_blue") . "\t\t" . NijiEcho::text("► Build a new Table Model") . "\n";
        echo "\t" . NijiEcho::text("create:migration")->color("light_blue") . "\t" . NijiEcho::text("► Build a new Migration") . "\n";
        echo "\t" . NijiEcho::text("create:grower")->color("light_blue") . "\t\t" . NijiEcho::text("► Make a new db grower (data filler)") . "\n";
        echo "\t" . NijiEcho::text("migrate")->color("light_blue") . "\t\t\t" . NijiEcho::text("► Run migrations") . "\n";
        echo "\t" . NijiEcho::text("migrate:refresh")->color("light_blue") . "\t\t" . NijiEcho::text("► Reset DB (DROP + CREATE + MIGRATE)") . "\n";
        echo "\t" . NijiEcho::text("migrate:grower")->color("light_blue") . "\t\t" . NijiEcho::text("► Seed that DB (add data)") . "\n";
        echo "\t" . NijiEcho::text("help")->color("light_blue") . "\t\t\t" . NijiEcho::text("► How to use Querychan") . "\n";
        echo "\t" . NijiEcho::text("status")->color("light_blue") . "\t\t\t" . NijiEcho::text("► Check db status") . "\n";
        echo "\t" . NijiEcho::text("about")->color("light_blue") . "\t\t\t" . NijiEcho::text("► About Querychan") . "\n";

        return;
    }

    public function getLogo() {
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

        return;
    }
}