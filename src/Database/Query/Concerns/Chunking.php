<?php

namespace RedSky\Framework\Database\Query\Concerns;

use Generator;

trait Chunking
{
    public function chunk(int $count, callable $callback): bool
    {
        $page = 1;

        do {
            $results = $this->limit($count)
                ->offset(($page - 1) * $count)
                ->get();

            $itemsCount = count($results);

            if ($itemsCount === 0) {
                break;
            }

            if ($callback($results, $page) === false) {
                return false;
            }

            $page++;

        } while ($itemsCount === $count);

        return true;
    }

    public function each(callable $callback, int $chunk = 1000): bool
    {
        return $this->chunk(
            $chunk,
            function ($rows) use ($callback) {
                foreach ($rows as $key => $row) {
                    if ($callback($row, $key) === false) {
                        return false;
                    }
                }

                return true;
            }
        );
    }

    public function cursor(): Generator
    {
        $stmt = $this->runSelect();

        while ($row = $stmt->fetch()) {
            yield $this->modelClass
                ? new $this->modelClass($row)
                : $row;
        }
    }

    public function lazy(): Generator
    {
        return $this->cursor();
    }
}