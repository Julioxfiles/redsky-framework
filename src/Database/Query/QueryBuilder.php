<?php
declare(strict_types=1);

namespace RedSky\Database\Query;

use PDO;
use RedSky\Database\Grammars\Grammar;
use RedSky\Database\Grammars\MySqlGrammar;
use RedSky\Database\Query\Concerns\{
    BuildsWhere,
    BuildsSelect,
    ExecutesQueries,
    Aggregates,
    Chunking,
    Mutations
};

class QueryBuilder
{
    use BuildsWhere;
    use BuildsSelect;
    use ExecutesQueries;
    use Aggregates;
    use Chunking;
    use Mutations;

    protected PDO $pdo;
    protected Grammar $grammar;

    protected string $table;
    protected ?string $modelClass;

    protected array $selects = ['*'];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $orders = [];

    protected ?int $limit = null;
    protected ?int $offset = null;

    protected array $groups = [];
    protected array $havings = [];

    protected bool $distinct = false;

    public function __construct(
        PDO $pdo,
        string $table,
        ?string $modelClass = null,
        ?Grammar $grammar = null
    ) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->modelClass = $modelClass;
        $this->grammar = $grammar ?? new MySqlGrammar();
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getSelects(): array
    {
        return $this->selects;
    }

    public function getWheres(): array
    {
        return $this->wheres;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getHavings(): array
    {
        return $this->havings;
    }

    public function isDistinct(): bool
    {
        return $this->distinct;
    }

    public function hasWheres(): bool
    {
        return !empty($this->wheres);
    }

    public function hasOrders(): bool
    {
        return !empty($this->orders);
    }
}