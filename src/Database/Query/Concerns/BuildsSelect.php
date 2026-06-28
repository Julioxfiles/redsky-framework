<?php

namespace RedSky\Database\Query\Concerns;

trait BuildsSelect
{
    public function select(array|string ...$columns): static
    {
        $this->selects = is_array($columns[0])
            ? $columns[0]
            : $columns;

        return $this;
    }

    public function distinct(): static
    {
        $this->distinct = true;
        return $this;
    }

    public function latest(string $column = 'created_at'): static
    {
        return $this->orderBy($column, 'DESC');
    }

    public function oldest(string $column = 'created_at'): static
    {
        return $this->orderBy($column, 'ASC');
    }

    public function inRandomOrder(): static
    {
        $this->orders[] = ['RAND()', ''];
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function take(int $limit): static
    {
        return $this->limit($limit);
    }

    public function skip(int $offset): static
    {
        return $this->offset($offset);
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orders[] = [$column, strtoupper($direction)];
        return $this;
    }

    public function orderByDesc(string $column): static
    {
        return $this->orderBy($column, 'DESC');
    }

    public function orderByAsc(string $column): static
    {
        return $this->orderBy($column, 'ASC');
    }
}