<?php
declare(strict_types=1);

namespace RedSky\Framework\Database\Grammars;

use RedSky\Framework\Database\Query\QueryBuilder;

class OracleGrammar extends Grammar
{
    public function wrap(string $value): string
    {
        if ($value === '*') {
            return $value;
        }

        if (
            str_contains($value, '(') ||
            str_contains($value, ')') ||
            str_contains($value, ' ')
        ) {
            return $value;
        }

        if (str_contains($value, '.')) {
            $segments = explode('.', $value);

            return implode('.', array_map(
                fn (string $segment) => '"' . strtoupper($segment) . '"',
                $segments
            ));
        }

        return '"' . strtoupper($value) . '"';
    }

    public function compileLimitOffset(QueryBuilder $query): string
    {
        $limit  = $query->getLimit();
        $offset = $query->getOffset();

        if ($limit === null && $offset === null) {
            return '';
        }

        $sql = '';

        if ($offset !== null) {
            $sql .= ' OFFSET ' . $offset . ' ROWS';
        }

        if ($limit !== null) {
            if ($offset === null) {
                $sql .= ' OFFSET 0 ROWS';
            }

            $sql .= ' FETCH NEXT ' . $limit . ' ROWS ONLY';
        }

        return $sql;
    }
}