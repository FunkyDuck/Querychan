<?php

namespace FunkyDuck\Querychan\ORM;

use FunkyDuck\Querychan\ORM\Database;
use PDO;

class SchemaBuilder {
    /** @var ColumnBuilder[] */
    protected array $columns = [];
    protected array $foreignKeys = [];
    protected array $indexes = [];

    protected function addColumn(ColumnBuilder $column): ColumnBuilder
    {
        $this->columns[] = $column;
        return $column;
    }
    
    public function id(): ColumnBuilder 
    {
        return $this->addColumn(new ColumnBuilder('id', 'id'));
    }

    public function int(string $name): ColumnBuilder {
        return $this->addColumn(new ColumnBuilder($name, 'int'));
    }
    
    public function bigint(string $name): ColumnBuilder {
        return $this->addColumn(new ColumnBuilder($name, 'bigint'));
    }
    
    public function char(string $name, int $length): ColumnBuilder {
        $column = new ColumnBuilder($name, "char");
        $column->options['length'] = $length;
        return $this->addColumn($column);
    }
    
    public function string(string $name, int $length = 255)
    {
        return $this->varchar($name, $length);
    }
    
    public function varchar(string $name, int $length): ColumnBuilder {
        $column = new ColumnBuilder($name, "varchar");
        $column->options['length'] = $length;
        return $this->addColumn($column);
    }
    
    public function text(string $name): ColumnBuilder {
        return $this->addColumn(new ColumnBuilder($name, 'text'));
    }
    
    public function json(string $name): ColumnBuilder {
        return $this->addColumn(new ColumnBuilder($name, 'json'));
    }
    
    public function bool(string $name): ColumnBuilder {
        return $this->addColumn(new ColumnBuilder($name, 'bool'));
    }
    
    public function decimal(string $name, int $precision, int $scale): ColumnBuilder {
        $column = new ColumnBuilder($name, "decimal");
        $column->options['precision'] = $precision;
        $column->options['scale'] = $scale;
        return $this->addColumn($column);
    }
    
    public function enum(string $name, array $values): ColumnBuilder {
        $column = new ColumnBuilder($name, 'varchar');
        $column->options['values'] = $values;
        return $this->addColumn($column);
    }
    
    public function date(string $name): ColumnBuilder {
        return $this->addColumn(new ColumnBuilder($name, 'date'));
    }
    
    public function time(string $name): ColumnBuilder {
        return $this->addColumn(new ColumnBuilder($name, 'time'));
    }
    
    public function datetime(string $name): ColumnBuilder {
        return $this->addColumn(new ColumnBuilder($name, 'datetime'));
    }
    
    public function timestamp(string $name): ColumnBuilder {
        return $this->addColumn(new ColumnBuilder($name, 'timestamp'));
    }
    
    public function timestamps(): void {
        $createdAt = new ColumnBuilder("created_at", "timestamp");
        $createdAt->default('CURRENT_TIMESTAMP');
        $this->addColumn($createdAt);

        $updatedAt = new ColumnBuilder("updated_at", "timestamp");
        $updatedAt->default('CURRENT_TIMESTAMP');
        $this->addColumn($updatedAt);
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
    
    public function toSql(string $table): string {
        $driver = Database::get()->getAttribute(PDO::ATTR_DRIVER_NAME);

        $columnsSql = array_map(
            fn(ColumnBuilder $column) => $column->toSql($driver),
            $this->columns
        );

        $columnsString = implode(', ', $columnsSql);
        return "CREATE TABLE IF NOT EXISTS `$table` ($columnsString)";
    }
}