<?php

namespace FunkyDuck\Querychan\ORM;

class ColumnBuilder {
    private string $definition;

    public function __construct(string $name, string $type) {
        $this->definition = "`$name` $type";
    }

    public function getDefinition(): string {
        return $this->definition;
    }

    public function nullable(): self {
        $this->definition .= " NULL";
        return $this;
    }

    public function notNull(): self {
        $this->definition .= " NOT NULL";
        return $this;
    }

    public function default(string|int|null $value): self {
        if(is_string($value) && strtoupper($value) === "CURRENT_TIMESTAMP") {
            $val = "CURRENT_TIMESTAMP";
        }
        elseif(is_string($value)) {
            $val = "'" . addslashes($value) . "'";
        }
        elseif($value === null) {
            $val = "NULL";
        }
        else {
            $val = $value;
        }
        $this->definition .= " DEFAULT $val";
        return $this;
    }

    public function unique(): self {
        $this->definition .= " UNIQUE";
        return $this;
    }
}