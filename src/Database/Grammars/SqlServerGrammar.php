<?php
declare(strict_types=1);

namespace RedSky\Database\Grammars;

use RedSky\Database\Query\QueryBuilder;

class SqlServerGrammar extends Grammar
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
                fn (string $segment) => "[{$segment}]",
                $segments
            ));
        }

        return "[{$value}]";
    }

    public function compileLimitOffset(QueryBuilder $query): string
    {
        $limit  = $query->getLimit();
        $offset = $query->getOffset();

        if ($limit === null && $offset === null) {
            return '';
        }

        $offset ??= 0;

        $sql = ' OFFSET ' . $offset . ' ROWS';

        if ($limit !== null) {
            $sql .= ' FETCH NEXT ' . $limit . ' ROWS ONLY';
        }

        return $sql;
    }
}