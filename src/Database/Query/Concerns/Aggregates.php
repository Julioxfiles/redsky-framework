<?php

namespace RedSky\Framework\Database\Query\Concerns;

trait Aggregates
{
    public function count(): int
    {
        $sql = $this->grammar->compileSelect($this, true);

        $stmt = $this->pdo->prepare($sql);

        foreach ($this->bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function sum(string $column): int|float
    {
        return $this->aggregate("SUM($column)");
    }

    public function avg(string $column): int|float
    {
        return $this->aggregate("AVG($column)");
    }

    public function min(string $column): mixed
    {
        return $this->aggregate("MIN($column)");
    }

    public function max(string $column): mixed
    {
        return $this->aggregate("MAX($column)");
    }

    protected function aggregate(string $expression): mixed
    {
        $original = $this->selects;

        $this->selects = [
            $expression . ' AS aggregate'
        ];

        $stmt = $this->runSelect();

        $row = $stmt->fetch();

        $this->selects = $original;

        return $row['aggregate'] ?? null;
    }
}