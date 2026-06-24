<?php
declare(strict_types=1);

namespace RedSky\Framework\Database\Grammars;

use RedSky\Framework\Database\Query\QueryBuilder;

abstract class Grammar
{
    public function wrap(string $value): string
    {
        return $value;
    }

    public function compileSelect(
        QueryBuilder $query,
        bool $count = false
    ): string {
        $select = $count
            ? 'COUNT(*)'
            : $this->compileColumns($query);

        $sql = 'SELECT ' . $select .
               ' FROM ' . $this->wrap($query->getTable());

        if ($query->hasWheres()) {
            $sql .= ' WHERE ' . $this->compileWheres($query);
        }

        if (!$count && !empty($query->getGroups())) {
            $sql .= ' ' . $this->compileGroups($query);
        }

        if (!$count && !empty($query->getHavings())) {
            $sql .= ' ' . $this->compileHavings($query);
        }

        if (!$count && $query->hasOrders()) {
            $sql .= ' ' . $this->compileOrders($query);
        }

        if (!$count) {
            $sql .= $this->compileLimitOffset($query);
        }

        return trim($sql);
    }

    protected function compileColumns(QueryBuilder $query): string
    {
        $columns = $this->columnize($query->getSelects());

        return $query->isDistinct()
            ? 'DISTINCT ' . $columns
            : $columns;
    }

    public function compileWheres(QueryBuilder $query): string
    {
        $parts = [];

        foreach ($query->getWheres() as $index => $where) {

            $prefix = $index === 0 ? '' : $where['type'] . ' ';
            $column = $this->wrap($where['column']);
            $operator = strtoupper($where['operator']);

            if ($operator === 'IS NULL' || $operator === 'IS NOT NULL') {
                $parts[] = $prefix . $column . ' ' . $operator;
                continue;
            }

            if ($operator === 'BETWEEN' || $operator === 'NOT BETWEEN') {
                $parts[] = $prefix . $column . ' ' . $operator . ' ' . $where['param'];
                continue;
            }

            if ($operator === 'IN') {
                $parts[] = $prefix . $column . ' IN ' . $where['param'];
                continue;
            }

            $parts[] = $prefix . $column . ' ' . $operator . ' ' . $where['param'];
        }

        return implode(' ', $parts);
    }

    public function compileGroups(QueryBuilder $query): string
    {
        return 'GROUP BY ' . $this->columnize($query->getGroups());
    }

    public function compileHavings(QueryBuilder $query): string
    {
        $parts = [];

        foreach ($query->getHavings() as $index => $having) {

            if (isset($having['raw'])) {
                $parts[] = $having['raw'];
                continue;
            }

            $prefix = $index === 0 ? '' : 'AND ';

            $parts[] =
                $prefix .
                $this->wrap($having['column']) .
                ' ' .
                $having['operator'] .
                ' ' .
                $having['param'];
        }

        return 'HAVING ' . implode(' ', $parts);
    }

    public function compileOrders(QueryBuilder $query): string
    {
        $parts = [];

        foreach ($query->getOrders() as $order) {

            if (strtoupper($order[0]) === 'RAND()') {
                $parts[] = 'RAND()';
                continue;
            }

            $parts[] = $this->wrap($order[0]) . ' ' . $order[1];
        }

        return 'ORDER BY ' . implode(', ', $parts);
    }

    public function columnize(array $columns): string
    {
        return implode(', ', array_map(
            fn ($col) => $col === '*' ? '*' : $this->wrap($col),
            $columns
        ));
    }

    public function compileLimitOffset(QueryBuilder $query): string
    {
        $sql = '';

        if ($query->getLimit() !== null) {
            $sql .= ' LIMIT ' . $query->getLimit();
        }

        if ($query->getOffset() !== null) {
            $sql .= ' OFFSET ' . $query->getOffset();
        }

        return $sql;
    }
}