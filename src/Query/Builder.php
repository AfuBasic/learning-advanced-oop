<?php

declare(strict_types=1);

namespace App\Query;

class Builder
{
    protected array $wheres = [];
    protected array $bindings = [];

    public function __construct(protected string $table) {}

    public function where(string $column, mixed $value, string $operator = '='): self
    {
        $this->wheres = [
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
}
