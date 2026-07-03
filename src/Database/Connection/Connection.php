<?php
declare(strict_types=1);

namespace RedSky\Framework\Database\Connection;

use PDO;
use PDOException;
use Exception;
use RedSky\Database\Grammars\{
    Grammar,
    MySqlGrammar,
    PostgresGrammar,
    SQLiteGrammar,
    SqlServerGrammar,
    OracleGrammar
};

class Connection
{
    protected static array $connections = [];
    protected static array $config = [];
    protected static string $default = 'default';

    public static function configure(array $config, string $default = 'default'): void
    {
        static::$config = $config;
        static::$default = $default;
    }

    public static function get(?string $name = null): PDO
    {
        $name ??= static::$default;

        if (!isset(static::$connections[$name])) {
            static::$connections[$name] = static::connect($name);
        }

        return static::$connections[$name];
    }

    public static function grammar(?string $name = null): Grammar
    {
        $name ??= static::$default;

        if (!isset(static::$config[$name])) {
            throw new Exception("Database connection [$name] not configured.");
        }

        $driver = strtolower(static::$config[$name]['driver'] ?? 'mysql');

        return match ($driver) {
            'mysql', 'mariadb' => new MySqlGrammar(),
            'pgsql', 'postgres', 'postgresql' => new PostgresGrammar(),
            'sqlite' => new SQLiteGrammar(),
            'sqlsrv', 'mssql' => new SqlServerGrammar(),
            'oracle', 'oci' => new OracleGrammar(),
            default => throw new Exception("Unsupported grammar driver [$driver]"),
        };
    }

    protected static function connect(string $name): PDO
    {
        if (!isset(static::$config[$name])) {
            throw new Exception("Database connection [$name] not configured.");
        }

        $config = static::$config[$name];

        try {
            return new PDO(
                static::dsn($config),
                $config['username'] ?? null,
                $config['password'] ?? null,
                static::options($config)
            );
        } catch (PDOException $e) {
            throw new Exception(
                "Database connection [$name] failed: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    protected static function dsn(array $config): string
    {
        return match ($config['driver']) {
            'mysql', 'mariadb' => sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['port'] ?? 3306,
                $config['database'],
                $config['charset'] ?? 'utf8mb4'
            ),

            'pgsql', 'postgres', 'postgresql' => sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                $config['host'],
                $config['port'] ?? 5432,
                $config['database']
            ),

            'sqlite' => "sqlite:" . $config['database'],

            'sqlsrv', 'mssql' => sprintf(
                "sqlsrv:Server=%s,%s;Database=%s",
                $config['host'],
                $config['port'] ?? 1433,
                $config['database']
            ),

            'oracle', 'oci' => sprintf(
                "oci:dbname=//%s:%s/%s;charset=%s",
                $config['host'],
                $config['port'] ?? 1521,
                $config['database'],
                $config['charset'] ?? 'AL32UTF8'
            ),

            default => throw new Exception("Unsupported driver [{$config['driver']}]"),
        };
    }

    protected static function options(array $config): array
    {
        return $config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    public static function begin(?string $name = null): void
    {
        static::get($name)->beginTransaction();
    }

    public static function commit(?string $name = null): void
    {
        static::get($name)->commit();
    }

    public static function rollback(?string $name = null): void
    {
        static::get($name)->rollBack();
    }

    public static function transaction(callable $callback, ?string $name = null): mixed
    {
        $pdo = static::get($name);

        try {
            $pdo->beginTransaction();
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function disconnect(?string $name = null): void
    {
        $name ??= static::$default;
        unset(static::$connections[$name]);
    }

    public static function disconnectAll(): void
    {
        static::$connections = [];
    }
}