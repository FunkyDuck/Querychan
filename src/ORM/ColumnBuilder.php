<?php

namespace FunkyDuck\Querychan\ORM;

class ColumnBuilder {
    protected string $name;
    protected string $type;
    public array $options = [];

    public function __construct(string $name, string $type) 
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function notNull(): static 
    {
        $this->options['nullable'] = false;
        return $this;
    }

    public function default($value): static 
    {
        $this->options['default'] = $value;
        return $this;
    }

    public function toSql(string $driver): string 
    {
        $sql = "`{$this->name}`";

        switch($this->type) {
            case 'id':
                return $driver === 'sqlite'
                    ? '`id` INTEGER PRIMARY KEY AUTOINCREMENT'
                    : '`id` INT AUTO_INCREMENT PRIMARY KEY';
            case 'varchar':
                $length = $this->options['length'] ?? 255;
                $sql .= " VARCHAR($length)";
                break;
            case 'enum': 
                $values = $this->options['values'] ?? [];
                $allowed = implode(', ', array_map(fn($v) => "'$v'", $values));
                if($driver === 'sqlite') {
                    $sql .= " TEXT CHECK(`{$this->name}` IN ($allowed))";
                }
                else {
                    $sql .= " ENUM($allowed)";
                }
                break;
            case 'timestamp':
                $sql .= $driver === 'sqlite' ? 'DATETIME' : 'TIMESTAMP';
                break;
        }

        if(isset($this->options['nullable']) && $this->options['nullable'] === false) {
            $sql .= ' NOT NULL';
        }

        if(isset($this->options['default'])) {
            $defaultValue = is_string($this->options['default']) ? "'{$this->options['default']}'" : $this->options['default'];
            $sql .= " DEFAULT {$defaultValue}";
        }

        return $sql;
    }
}