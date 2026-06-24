<?php

namespace RedSky\Framework\Database\Query\Concerns;

trait ExecutesQueries
{
    public function get(): array
    {
        $stmt = $this->runSelect();
        $rows = $stmt->fetchAll();

        return $this->hydrate($rows);
    }

    public function all(): array
    {
        return $this->get();
    }

    public function first(): mixed
    {
        $this->limit(1);

        $stmt = $this->runSelect();
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->modelClass
            ? new $this->modelClass($row)
            : $row;
    }

    public function firstOrFail(): mixed
    {
        $result = $this->first();

        if (!$result) {
            throw new \Exception('Record not found.');
        }

        return $result;
    }

    public function sole(): mixed
    {
        $results = $this->limit(2)->get();

        $count = count($results);

        if ($count === 0) {
            throw new \Exception('No records found.');
        }

        if ($count > 1) {
            throw new \Exception('Multiple records found.');
        }

        return $results[0];
    }

    public function find(mixed $id, string $column = 'id'): mixed
    {
        return $this->where($column, '=', $id)->first();
    }

    public function findOrFail(mixed $id, string $column = 'id'): mixed
    {
        $result = $this->find($id, $column);

        if (!$result) {
            throw new \Exception('Record not found.');
        }

        return $result;
    }

    public function firstWhere(string $column, string $operator, mixed $value): mixed
    {
        return $this->where($column, $operator, $value)->first();
    }

    public function value(string $column): mixed
    {
        $row = $this->select($column)->first();

        if (!$row) {
            return null;
        }

        return $this->modelClass
            ? $row->get($column)
            : ($row[$column] ?? null);
    }

    public function pluck(string $column): array
    {
        $rows = $this->select($column)->get();

        return array_map(function ($row) use ($column) {
            return $this->modelClass
                ? $row->get($column)
                : ($row[$column] ?? null);
        }, $rows);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $page = max($page, 1);

        $total = $this->count();
        $lastPage = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $data = $this->limit($perPage)
            ->offset($offset)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $lastPage,
            ],
        ];
    }

    protected function runSelect()
    {
        $sql = $this->grammar->compileSelect($this);

        $stmt = $this->pdo->prepare($sql);

        foreach ($this->bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt;
    }

    protected function hydrate(array $rows): array
    {
        if (!$this->modelClass) {
            return $rows;
        }

        return array_map(
            fn ($row) => new $this->modelClass($row),
            $rows
        );
    }

    public function toSql(): string
    {
        return $this->grammar->compileSelect($this);
    }

    public function dump(): static
    {
        var_dump([
            'sql'      => $this->toSql(),
            'bindings' => $this->bindings,
        ]);

        return $this;
    }

    public function dd(): never
    {
        $this->dump();
        exit;
    }
}