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
            case 'int':
                $sql .= " INT";
                break;
            case 'bigint':
                $sql .= " BIGINT";
                break;
            case 'decimal':
                $precision = $this->options['precision'] ?? 8;
                $scale = $this->options['scale'] ?? 2;
                $sql .= " DECIMAL($precision, $scale)";
                break;
            case 'char':
                $length = $this->options['length'] ?? 255;
                $sql .= " VARCHAR($length)";
                break;
            case 'varchar':
                $length = $this->options['length'] ?? 255;
                $sql .= " VARCHAR($length)";
                break;
            case 'text':
                $sql .= " TEXT";
                break; 
            case 'json':
                $sql .= $driver === 'sqlite' ? ' TEXT' : ' JSON';
                break; 
            case 'bool':
                $sql .= $driver === 'sqlite' ? ' INTEGER' : ' BOOLEAN';
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
            case 'date':
                $sql .= ' DATE';
                break;
            case 'time':
                $sql .= ' TIME';
                break;
            case 'datetime':
                $sql .= ' DATETIME';
                break;
            case 'timestamp':
                $sql .= $driver === 'sqlite' ? 'DATETIME' : 'TIMESTAMP';
                break;
            default:
                throw new \Exception("Unsupported Column type: {$this->type}");
        }

        if(isset($this->options['nullable']) && $this->options['nullable'] === false) {
            $sql .= ' NOT NULL';
        }

        if(isset($this->options['default'])) {
            $defaultValue = $this->options['default'];
            if(is_string($defaultValue) && strtoupper($defaultValue) === 'CURRENT_TIMESTAMP') {
                if($driver === 'sqlite') {
                    $sql .= " DEFAULT CURRENT_TIMESTAMP";
                }
                else {
                    $sql .= " DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                }
            }
            else {
                $formattedDefault = is_string($defaultValue) ? "'{$defaultValue}'" : $defaultValue;
                $sql .= " DEFAULT {$formattedDefault}";
            }
        }

        return $sql;
    }
}