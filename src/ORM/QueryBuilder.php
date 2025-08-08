<?php

namespace Querychan\ORM;

use Querychan\ORM\Database;
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
        $column = htmlspecialchars($column);
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

        if($this->where) {
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

    public function insert(array $data): bool {
        $table = $this->modelClass::getTable();
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES(" . implode(', ', $placeholders) . ")";
        $stmt = Database::get()->prepare($sql);

        foreach ($data as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }

        return $stmt->execute();
    }
}