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

        $lines = file($path, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {

            $line = trim($line);

            // skip empty lines
            if ($line === '') {
                continue;
            }

            // skip comments (# or //)
            if (str_starts_with($line, '#') || str_starts_with($line, '//')) {
                continue;
            }

            // remove optional export keyword
            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }

            // split only first =
            $pos = strpos($line, '=');

            if ($pos === false) {
                continue; // invalid line, ignore safely
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            // remove quotes safely
            $value = trim($value, "\"'");

            if ($key === '') {
                continue;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
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