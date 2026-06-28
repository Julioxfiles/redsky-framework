<?php
declare(strict_types=1);

namespace RedSky\Database\Grammars;

use RedSky\Database\Query\QueryBuilder;

class SQLiteGrammar extends Grammar
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
                fn (string $segment) => "\"{$segment}\"",
                $segments
            ));
        }

        return "\"{$value}\"";
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