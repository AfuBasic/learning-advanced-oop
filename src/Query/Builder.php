<?php

declare(strict_types=1);

namespace App\Query;

use App\Database\Connection;

class Builder
{
    protected array $wheres = [];
    protected array $bindings = [];

    public function __construct(protected string $table) {}

    public function where(string $column, mixed $value, string $operator = '='): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        $paramName = $column . '_' . count($this->wheres);
        $this->bindings[$paramName] = $value;

        return $this;
    }

    public function toSqlAndBindings(): array
    {
        return [
            'table' => $this->table,
            'wheres' => $this->wheres,
            'bindings' => $this->bindings
        ];
    }

    public function get(): array
    {
        $grammar = new Grammar();
        $sql = $grammar->compileSelect($this);

        return Connection::resolve()->select($sql, $this->bindings);
    }
    public function getTable(): string
    {
        return $this->table;
    }
    public function getWheres(): array
    {
        return $this->wheres;
    }
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
