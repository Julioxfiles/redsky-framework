<?php
declare(strict_types=1);

namespace RedSky\Database;

use PDO;
use Exception;
use JsonSerializable;
use RedSky\Database\Query\QueryBuilder;
use RedSky\Database\Grammars\Grammar;

abstract class Model implements JsonSerializable
{
    protected array $hidden = [];

    protected static PDO $db;
    protected static Grammar $grammar;

    protected static array $booted = [];

    protected static array $events = [
        'creating' => [],
        'created'  => [],
        'updating' => [],
        'updated'  => [],
        'saving'   => [],
        'saved'    => [],
        'deleting' => [],
        'deleted'  => [],
    ];

    protected string $table;
    protected string $primaryKey = 'id';

    protected bool $incrementing = false;
    protected string $keyType = 'string';

    protected bool $timestamps = true;
    protected bool $softDeletes = false;

    protected string $createdAt = 'created_at';
    protected string $updatedAt = 'updated_at';
    protected string $deletedAt = 'deleted_at';

    protected array $fillable = [];
    protected array $guarded = ['id'];

    protected array $attributes = [];
    protected array $original = [];
    protected array $changes = [];

    protected bool $strict = true;

    public function __construct(array $attributes = [])
    {
        static::bootIfNotBooted();

        if (!isset($this->table)) {
            $this->table = $this->inferTableName();
        }

        $this->attributes = $attributes;
        $this->syncOriginal();
    }

    public static function setConnection(PDO $pdo): void
    {
        static::$db = $pdo;
    }

    public static function setGrammar(Grammar $grammar): void
    {
        static::$grammar = $grammar;
    }

    protected function getConnection(): PDO
    {
        return static::$db;
    }

    protected static function bootIfNotBooted(): void
    {
        $class = static::class;

        if (!isset(self::$booted[$class])) {
            self::$booted[$class] = true;

            if (method_exists($class, 'boot')) {
                forward_static_call([$class, 'boot']);
            }
        }
    }

    protected static function registerEvent(string $event, callable $callback): void
    {
        static::$events[$event][] = $callback;
    }

    protected function fireEvent(string $event): void
    {
        foreach (static::$events[$event] ?? [] as $callback) {
            $callback($this);
        }
    }

    protected function inferTableName(): string
    {
        return strtolower(class_basename(static::class)) . 's';
    }

    public function fill(array $data): static
    {
        foreach ($data as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    protected function isFillable(string $key): bool
    {
        if (!empty($this->fillable)) {
            return in_array($key, $this->fillable, true);
        }

        return !in_array($key, $this->guarded, true);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function set(string $key, mixed $value): static
    {
        if ($this->isFillable($key)) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function toArray(): array
    {
        $array = $this->attributes;

        foreach ($this->hidden as $key) {
            unset($array[$key]);
        }

        return $array;
    }

    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    public function getDirty(): array
    {
        return array_diff_assoc($this->attributes, $this->original);
    }

    public function isDirty(): bool
    {
        return !empty($this->getDirty());
    }

    public function isClean(): bool
    {
        return !$this->isDirty();
    }

    public function wasChanged(): bool
    {
        return !empty($this->changes);
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public static function query(): QueryBuilder
    {
        $model = new static;

        return new QueryBuilder(
            static::$db,
            $model->table,
            static::class,
            static::$grammar
        );
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function find(mixed $id): ?static
    {
        return static::query()
            ->where((new static)->primaryKey, '=', $id)
            ->first();
    }

    public static function where(string $column, mixed $operator = null, mixed $value = null): QueryBuilder
    {
        // Si solo pasan 2 argumentos: where('email', 'test@mail.com')
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        return static::query()->where($column, $operator, $value);
    }

    public static function paginate(int $perPage = 15, int $page = 1): array
    {
        return static::query()->paginate($perPage, $page);
    }

    public static function create(array $attributes): static
    {
        $model = new static();

        if (!isset($attributes[$model->primaryKey])) {
            $attributes[$model->primaryKey] = uuid();
        }

        $model->fill($attributes);
        $model->save();

        return $model;
    }

    public function save(): bool
    {
        $this->fireEvent('saving');

        $result = isset($this->attributes[$this->primaryKey]) &&
                  isset($this->original[$this->primaryKey])
            ? $this->performUpdate()
            : $this->performInsert();

        if ($result) {
            $this->fireEvent('saved');
        }

        return $result;
    }

    protected function performInsert(): bool
    {
        $this->fireEvent('creating');

        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');

            $this->attributes[$this->createdAt] = $now;
            $this->attributes[$this->updatedAt] = $now;
        }

        $result = static::query()->insert($this->attributes);

        if ($result) {
            $this->syncOriginal();
            $this->fireEvent('created');
        }

        return $result;
    }

    protected function performUpdate(): bool
    {
        $this->fireEvent('updating');

        if ($this->timestamps) {
            $this->attributes[$this->updatedAt] = date('Y-m-d H:i:s');
        }

        $dirty = array_diff_assoc($this->attributes, $this->original);
        unset($dirty[$this->primaryKey]);

        if (empty($dirty)) {
            return true;
        }

        $result = static::query()
            ->where($this->primaryKey, '=', $this->attributes[$this->primaryKey])
            ->update($dirty) > 0;

        if ($result) {
            $this->syncOriginal();
            $this->fireEvent('updated');
        }

        return $result;
    }

    public function delete(): bool
    {
        $this->fireEvent('deleting');

        if ($this->softDeletes) {
            $this->attributes[$this->deletedAt] = date('Y-m-d H:i:s');
            return $this->save();
        }

        $sql = sprintf(
            "DELETE FROM %s WHERE %s = :id",
            $this->table,
            $this->primaryKey
        );

        $stmt = $this->getConnection()->prepare($sql);

        $result = $stmt->execute([
            'id' => $this->attributes[$this->primaryKey],
        ]);

        if ($result) {
            $this->fireEvent('deleted');
        }

        return $result;
    }

    public static function first(): ?static
    {
        return static::query()->first();
    }

    public static function firstWhere(string $column, string $operator, mixed $value): ?static
    {
        return static::query()->where($column, $operator, $value)->first();
    }

    public function update(array $data): bool
    {
        $this->fill($data);
        return $this->save();
    }

    public function refresh(): static
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            throw new Exception('Cannot refresh model without primary key');
        }

        $fresh = static::find($this->attributes[$this->primaryKey]);

        if (!$fresh) {
            throw new Exception('Model no longer exists');
        }

        $this->attributes = $fresh->attributes;
        $this->syncOriginal();

        return $this;
    }

    public function fresh(): ?static
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return null;
        }

        return static::find($this->attributes[$this->primaryKey]);
    }

    public function increment(string $column, int $value = 1): bool
    {
        $this->attributes[$column] = ($this->attributes[$column] ?? 0) + $value;
        return $this->save();
    }

    public function decrement(string $column, int $value = 1): bool
    {
        $this->attributes[$column] = ($this->attributes[$column] ?? 0) - $value;
        return $this->save();
    }

    public static function destroy(mixed $id): bool
    {
        $model = static::find($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    public static function count(): int
    {
        return static::query()->count();
    }

    public static function exists(): bool
    {
        return static::query()->exists();
    }

    public static function findOrFail(mixed $id): static
    {
        $model = static::find($id);

        if (!$model) {
            throw new Exception('Model not found');
        }

        return $model;
    }

    public static function store(array $attributes): static
    {
        return static::create($attributes);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function __get(string $key): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        $method = 'get' . ucfirst($key) . 'Attribute';

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        if ($this->strict) {
            throw new Exception("Undefined property [$key]");
        }

        return null;
    }

    public function __set(string $key, mixed $value): void
    {
        $method = 'set' . ucfirst($key) . 'Attribute';

        if (method_exists($this, $method)) {
            $this->$method($value);
            return;
        }

        if ($this->isFillable($key)) {
            $this->attributes[$key] = $value;
            return;
        }

        if ($this->strict) {
            throw new Exception("Cannot set [$key]");
        }
    }

    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->attributes)
            || method_exists($this, 'get' . ucfirst($key) . 'Attribute');
    }

    public function __unset(string $key): void
    {
        if (array_key_exists($key, $this->attributes)) {
            unset($this->attributes[$key]);
            return;
        }

        if ($this->strict) {
            throw new Exception("Cannot unset [$key]");
        }
    }
}