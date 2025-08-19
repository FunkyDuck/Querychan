<?php

namespace FunkyDuck\Querychan\ORM;

use PDO;

class SchemaBuilder {
    /** @var ColumnBuilder[] */
    private array $columns = [];
    private array $indexes = [];
    private array $foreignKeys = [];
    
    public function toSql(string $table): string {
        $colsSql = array_map(fn($col) => $col->getDefinition(), $this->columns);
        $columnsSql = implode(",\n ", $colsSql);
        $foreignSql = implode(",\n ", $this->foreignKeys);
        $indexesSql = implode(",\n ", $this->indexes);
        
        $sql = "CREATE TABLE IF NOT EXISTS `$table` (\n $columnsSql";
        if(!empty($foreignSql)) {
            $sql .= ",\n $foreignSql";
        }
        if(!empty($indexesSql)) {
            $sql .= ",\n $indexesSql";
        }
        $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        return $sql;
    }
    
    public function id(string $name='id'): ColumnBuilder {
        $column = new ColumnBuilder($name, "INT PRIMARY KEY AUTO_INCREMENT");
        $this->columns[] = $column;
        return $column;
    }
    
    public function int(string $name): ColumnBuilder {
        $column = new ColumnBuilder($name, "INT");
        $this->columns[] = $column;
        return $column;
    }
    
    public function bigint(string $name): ColumnBuilder {
        $column = new ColumnBuilder($name, "BIGINT");
        $this->columns[] = $column;
        return $column;
    }
    
    public function char(string $name, int $length): ColumnBuilder {
        $column = new ColumnBuilder($name, "CHAR($length)");
        $this->columns[] = $column;
        return $column;
    }
    
    public function varchar(string $name, int $length): ColumnBuilder {
        $column = new ColumnBuilder($name, "VARCHAR($length)");
        $this->columns[] = $column;
        return $column;
    }
    
    public function text(string $name): ColumnBuilder {
        $column = new ColumnBuilder($name, "TEXT");
        $this->columns[] = $column;
        return $column;
    }
    
    public function json(string $name): ColumnBuilder {
        $column = new ColumnBuilder($name, "JSON");
        $this->columns[] = $column;
        return $column;
    }
    
    public function bool(string $name): ColumnBuilder {
        $column = new ColumnBuilder($name, "BOOLEAN");
        $this->columns[] = $column;
        return $column;
    }
    
    public function decimal(string $name, int $precision, int $scale): ColumnBuilder {
        $column = new ColumnBuilder($name, "DECIMAL($precision, $scale)");
        $this->columns[] = $column;
        return $column;
    }
    
    public function date(string $name): ColumnBuilder {
        $column = new ColumnBuilder($name, "DATE");
        $this->columns[] = $column;
        return $column;
    }
    
    public function time(string $name): ColumnBuilder {
        $column = new ColumnBuilder($name, "TIME");
        $this->columns[] = $column;
        return $column;
    }
    
    public function datetime(string $name): ColumnBuilder {
        $column = new ColumnBuilder($name, "DATETIME");
        $this->columns[] = $column;
        return $column;
    }
    
    public function enum(string $name, array $values): ColumnBuilder {
        $escaped = array_map(fn($val) => "'" . addslashes($val) . "'", $values);
        $column = new ColumnBuilder($name, "ENUM(" . implode(', ', $escaped) . ")");
        $this->columns[] = $column;
        return $column;
    }
    
    public function timestamp(string $name): ColumnBuilder {
        $column = new ColumnBuilder($name, "TIMESTAMP");
        $this->columns[] = $column;
        return $column;
    }

    public function timestamps(): self {
        $this->columns[] = new ColumnBuilder("created_at", "DATETIME DEFAULT CURRENT_TIMESTAMP");
        $this->columns[] = new ColumnBuilder("updated_at", "DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        return $this;
    }
    
    public function foreign(string $column, string $refTable, string $refColumn, string $onDelete = 'CASCADE', string $onUpdate = 'CASCADE'): self {
        $this->foreignKeys[] = "FOREIGN KEY (`$column`) REFERENCES `$refTable`(`$refColumn`) ON DELETE $onDelete ON UPDATE $onUpdate";
        return $this;
    }

    public function index(string|array $columns): self {
        $cols = is_array($columns) ? $columns : [$columns];
        $cols = array_map(fn($c) => "`$c`", $cols);
        $this->indexes[] = "INDEX (" . implode(", ", $cols) . ")";
        return $this;
    }
    
    public function uniqueIndex(string|array $columns): self {
        $cols = is_array($columns) ? $columns : [$columns];
        $cols = array_map(fn($c) => "`$c`", $cols);
        $this->indexes[] = "UNIQUE (" . implode(", ", $cols) . ")";    
        return $this;
    }
}