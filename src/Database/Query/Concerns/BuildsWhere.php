<?php

namespace RedSky\Framework\Database\Query\Concerns;

trait BuildsWhere
{
    public function where(
        string $column,
        string $operator,
        mixed $value = null
    ): static {
        $param = ':w_' . count($this->bindings);

        $this->wheres[] = [
            'type'     => 'AND',
            'column'   => $column,
            'operator' => $operator,
            'param'    => $param,
        ];

        $this->bindings[$param] = $value;

        return $this;
    }

    public function orWhere(
        string $column,
        string $operator,
        mixed $value
    ): static {
        $param = ':w_' . count($this->bindings);

        $this->wheres[] = [
            'type'     => 'OR',
            'column'   => $column,
            'operator' => $operator,
            'param'    => $param,
        ];

        $this->bindings[$param] = $value;

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $placeholders = [];

        foreach ($values as $value) {
            $key = ':in_' . count($this->bindings);

            $placeholders[] = $key;
            $this->bindings[$key] = $value;
        }

        $this->wheres[] = [
            'type'     => 'AND',
            'column'   => $column,
            'operator' => 'IN',
            'param'    => '(' . implode(',', $placeholders) . ')',
        ];

        return $this;
    }

    public function whereNull(string $column): static
    {
        $this->wheres[] = [
            'type'     => 'AND',
            'column'   => $column,
            'operator' => 'IS NULL',
            'param'    => null,
        ];

        return $this;
    }

    public function whereNotNull(string $column): static
    {
        $this->wheres[] = [
            'type'     => 'AND',
            'column'   => $column,
            'operator' => 'IS NOT NULL',
            'param'    => null,
        ];

        return $this;
    }

    public function whereBetween(string $column, array $values): static
    {
        $start = ':w_' . count($this->bindings);
        $this->bindings[$start] = $values[0];

        $end = ':w_' . count($this->bindings);
        $this->bindings[$end] = $values[1];

        $this->wheres[] = [
            'type'     => 'AND',
            'column'   => $column,
            'operator' => 'BETWEEN',
            'param'    => "$start AND $end",
        ];

        return $this;
    }

    public function whereNotBetween(string $column, array $values): static
    {
        $start = ':w_' . count($this->bindings);
        $this->bindings[$start] = $values[0];

        $end = ':w_' . count($this->bindings);
        $this->bindings[$end] = $values[1];

        $this->wheres[] = [
            'type'     => 'AND',
            'column'   => $column,
            'operator' => 'NOT BETWEEN',
            'param'    => "$start AND $end",
        ];

        return $this;
    }

    public function whereDate(string $column, string $date): static
    {
        $param = ':w_' . count($this->bindings);
        $this->bindings[$param] = $date;

        $this->wheres[] = [
            'type'     => 'AND',
            'column'   => "DATE($column)",
            'operator' => '=',
            'param'    => $param,
        ];

        return $this;
    }

    public function whereLike(string $column, string $value): static
    {
        return $this->where($column, 'LIKE', $value);
    }

    public function orWhereLike(string $column, string $value): static
    {
        return $this->orWhere($column, 'LIKE', $value);
    }

    public function when(bool $condition, callable $callback): static
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function unless(bool $condition, callable $callback): static
    {
        if (!$condition) {
            $callback($this);
        }

        return $this;
    }

    public function tap(callable $callback): static
    {
        $callback($this);
        return $this;
    }
}