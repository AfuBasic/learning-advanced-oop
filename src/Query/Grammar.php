<?php

declare(strict_types=1);

namespace App\Query;

use App\Query\Builder;

class Grammar
{
    public function compileSelect(Builder $builder): string
    {
        $table = $builder->getTable();
        $wheres = $builder->getWheres();

        $sql = "Select * FROM {$table}";

        if (empty($wheres)) {
            return $sql . ";";
        }

        $fragments = [];

        foreach ($wheres as $index => $where) {
            $paramNumber = $index + 1;
            $placeholder = ":" . $where['column'] . "_" . $paramNumber;
            $fragments[] = "{$where['column']} {$where['operator']} {$placeholder}";
        }

        $sql .= " WHERE " . implode(' AND ', $fragments);

        return $sql . ";";
    }
}
