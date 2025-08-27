<?php

namespace FunkyDuck\Querychan\ORM;

use FunkyDuck\Querychan\ORM\Database;
use FunkyDuck\Querychan\ORM\SchemaBuilder;
use FunkyDuck\Querychan\ORM\QueryBuilder;
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
        return (new QueryBuilder(static::class))->where('id', $id)->first();
    }

    public static function where(string $column, $value): QueryBuilder {
        return (new QueryBuilder(static::class))->where($column, $value);
    }

    public function save(): bool {
        $builder = new QueryBuilder(static::class);
        $attributes = $this->attributes;

        if(isset($attributes['id'])) {
            // UPDATE
            $where = ['id' => $attributes['id']];
            unset($attributes['id']);
            return $builder->update($where, $attributes);
        }
        else {
            // INSERT
            $newId = $builder->insert($attributes);
            if($newId) {
                $this->id = $newId;
                return true;
            }
            return false;
        }
    }

    public function delete(): bool {
        if(!isset($this->attributes['id'])) {
            return false;
        }

        $builder = new QueryBuilder((static::class));
        $where = ['id' => $this->id];

        return $builder->delete($where);
    }

    // TODO :: REMOVE COMMENTS LINE
    // public static function migrate(): void {
        //     $table = static::getTable();
        //     $schema = static::schema();
        
        //     $sql = $schema->toSql($table);
        
        //     Database::get()->exec($sql);
        // }
        
    public static function getTable(): string {
        if(isset(static::$table)) {
            return static::$table;
        }
            
        $parts = explode('\\', static::class);
        return strtolower(end($parts)) . 's';
    }
        
    // TODO :: REMOVE COMMENTS LINES
    // abstract protected static function schema(): SchemaBuilder;
}