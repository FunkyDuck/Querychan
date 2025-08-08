<?php

namespace Querychan\ORM;

use Querychan\ORM\Database;
use Querychan\ORM\SchemaBuilder;
use Querychan\ORM\QueryBuilder;
use PDO;

abstract class Model {
    protected static string $table;
    protected array $attributes = [];

    public function __construct(array $attributes = []) {
        $this->attributes = $attributes;
    }

    public function __get(string $key) {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value) {
        $this->attributes[$key] = $value;
    }

    public static function find(int $id): ?static {
        $table = static::getTable();
        $stmt = Database::get()->prepare("SELECT * FROM `$table` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new static($data) : null;
    }

    public static function where(string $column, $value): QueryBuilder {
        return (new QueryBuilder(static::class))->where($column, $value);
    }

    public function save(): void {
        $table = static::getTable();
        $columns = array_keys($this->attributes);
        $data = $this->attributes;

        if(isset($data['id'])) {
            $setClause = implode(', ', array_map(fn($col) => "$col = :$col", $columns));
            $stmt = Database::get()->prepare("UPDATE `$table` SET $setClause WHERE id = :id");
        }
        else {
            $fields = implode(', ', $columns);
            $placeholders = implode(', ', array_map(fn($col) => ":$col", $columns));
            $stmt = Database::get()->prepare("INSERT INTO `$table` ($fields) VALUES ($placeholders)");
        }

        $stmt->execute($data);
    }

    public static function migrate(): void {
        $table = static::getTable();
        $schema = static::schema();

        $sql = $schema->toSql($table);

        Database::get()->exec($sql);
    }

    protected static function getTable(): string {
        return static::$table ?? strtolower(static::class) . 's';
    }

    abstract protected static function schema(): SchemaBuilder;
}