<?php

declare(strict_types=1);

namespace RedSky\Framework\Support;

use RuntimeException;

class Env
{
    protected static bool $loaded = false;

    public static function load(string $path): void
    {
        if (static::$loaded) {
            return;
        }

        if (!file_exists($path)) {
            throw new RuntimeException("Environment file not found: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {

            $line = trim($line);

            // skip empty lines
            if ($line === '') {
                continue;
            }

            // skip comments
            if (str_starts_with($line, '#') || str_starts_with($line, '//')) {
                continue;
            }

            // remove export keyword (bash style)
            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }

            // must contain =
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            // remove surrounding quotes safely
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // handle escaped characters (basic support)
            $value = str_replace('\n', "\n", $value);
            $value = str_replace('\r', "\r", $value);
            $value = str_replace('\t', "\t", $value);

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }

        static::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key]
            ?? $_SERVER[$key]
            ?? getenv($key)
            ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_ENV[$key])
            || isset($_SERVER[$key])
            || getenv($key) !== false;
    }
}