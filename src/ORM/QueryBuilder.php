<?php

namespace FunkyDuck\Querychan\ORM;

use FunkyDuck\Querychan\ORM\Database;
use PDO;

class QueryBuilder {
    protected string $modelClass;
    protected array $where = [];
    protected ?string $orderBy = null;
    protected ?int $limit = null;

    public function __construct(string $modelClass) {
        $this->modelClass = $modelClass;
    }

    public function where(string $column, $value): static {
        $this->where[$column] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static {
        $direction = strtoupper($direction);
        $direction = ($direction !== 'ASC' && $direction !== 'DESC') ? 'ASC' : $direction;
        $this->orderBy = "$column $direction";
        return $this;
    }

    public function limit(int $limit): static {
        $limit = (int)$limit;
        if($limit <= 0 || $limit > 1000) {
            $limit = 100;
        }
        $this->limit = $limit;
        return $this;
    }

    public function get(): array {
        $table = $this->modelClass::getTable();
        $sql = "SELECT * FROM `$table`";
        $params = [];

        if(!empty($this->where)) {
            $conditions = [];
            foreach ($this->where as $col => $val) {
                $conditions[] = "$col = :$col";
                $params[$col] = $val;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        if($this->orderBy) {
            $sql .= " ORDER BY " . $this->orderBy;
        }

        if($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        $stmt = Database::get()->prepare($sql);
        $stmt->execute($params);

        $results = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new $this->modelClass($row);
        }

        return $results;
    }

    public function first(): ?object {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function insert(array $data): int|false {
        $table = $this->modelClass::getTable();
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES(" . implode(', ', $placeholders) . ")";
        $stmt = Database::get()->prepare($sql);

        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }

        if($stmt->execute()) {
            return (int)Database::get()->lastInsertId();
        }
        return false;
    }

    public function update(array $where, array $data): bool {
        $table = $this->modelClass::getTable();

        $setClauses = [];
        foreach (array_keys($data) as $col) {
            $setClauses[] = "`$col` = :set_$col";
        }
        $setString = implode(', ', $setClauses);

        $whereClauses = [];
        foreach (array_keys($where) as $col) {
            $whereClauses[] = "`$col` = :where_$col";
        }
        $whereString = implode(' AND ', $whereClauses);

        $sql = "UPDATE `$table` SET $setString WHERE $whereString";
        $stmt = Database::get()->prepare($sql);

        foreach($data as $col => $val) {
            $stmt->bindValue(":set__$col", $val);
        }

        foreach($where as $col => $val) {
            $stmt->bindValue(":where__$col", $val);
        }

        return $stmt->execute();
    }

    public function delete(array $data): bool {
        $table = $this->modelClass::getTable();
        return false;
    }
}