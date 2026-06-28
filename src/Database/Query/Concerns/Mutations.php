<?php

namespace RedSky\Database\Query\Concerns;

trait Mutations
{
    public function insert(array $data): bool
    {
        $columns = array_keys($data);

        $placeholders = [];
        $bindings = [];

        foreach ($data as $column => $value) {
            $key = ':' . $column;

            $placeholders[] = $key;
            $bindings[$key] = $value;
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($bindings);
    }

    public function insertGetId(array $data): string|int|false
    {
        $this->insert($data);
        return $this->pdo->lastInsertId();
    }

    public function update(array $data): int
    {
        $sets = [];
        $bindings = $this->bindings;

        foreach ($data as $column => $value) {
            $key = ':u_' . $column;

            $sets[] = "$column = $key";
            $bindings[$key] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);

        if ($this->hasWheres()) {
            $sql .= ' WHERE ' . $this->grammar->compileWheres($this);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";

        if ($this->hasWheres()) {
            $sql .= ' WHERE ' . $this->grammar->compileWheres($this);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    public function truncate(): bool
    {
        $sql = "TRUNCATE TABLE {$this->table}";

        return $this->pdo->exec($sql) !== false;
    }

    public function upsert(
        array $data,
        array $uniqueBy,
        array $updateColumns = []
    ): bool {
        $columns = array_keys($data);

        $placeholders = [];
        $bindings = [];

        foreach ($data as $column => $value) {
            $key = ':' . $column;

            $placeholders[] = $key;
            $bindings[$key] = $value;
        }

        if (empty($updateColumns)) {
            $updateColumns = array_diff($columns, $uniqueBy);
        }

        $updates = [];

        foreach ($updateColumns as $column) {
            $updates[] = "$column = VALUES($column)";
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)
             ON DUPLICATE KEY UPDATE %s",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders),
            implode(', ', $updates)
        );

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($bindings);
    }
}