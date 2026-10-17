<?php

declare(strict_types=1);

namespace RedSky\Framework\Support;

use RuntimeException;

/**
 * Loads and retrieves environment variables.
 *
 * Environment variables loaded from files are stored internally
 * and do not modify PHP global environment variables.
 *
 * @package RedSky\Framework\Support
 */
class Env
{
    /**
     * Environment variables loaded from files.
     *
     * @var array<string, string>
     */
    protected static array $variables = [];

    /**
     * Environment files already loaded.
     *
     * @var array<string, bool>
     */
    protected static array $loadedFiles = [];

    /**
     * Load environment variables from a file.
     *
     * Variables from subsequently loaded files overwrite
     * existing values with the same name.
     *
     * @param string $path Environment file path.
     *
     * @return void
     *
     * @throws RuntimeException If the file does not exist or cannot be read.
     */
    public static function load(string $path): void
    {
        $resolvedPath = realpath($path);

        if ($resolvedPath === false) {
            throw new RuntimeException(
                "Environment file not found: {$path}"
            );
        }

        if (isset(static::$loadedFiles[$resolvedPath])) {
            return;
        }

        if (!is_file($resolvedPath)) {
            throw new RuntimeException(
                "Environment path is not a file: {$resolvedPath}"
            );
        }

        $lines = file(
            $resolvedPath,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        if ($lines === false) {
            throw new RuntimeException(
                "Unable to read environment file: {$resolvedPath}"
            );
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if (
                $line === ''
                || str_starts_with($line, '#')
                || str_starts_with($line, '//')
            ) {
                continue;
            }

            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $value = str_replace(
                ['\\n', '\\r', '\\t'],
                ["\n", "\r", "\t"],
                $value
            );

            static::$variables[$key] = $value;
        }

        static::$loadedFiles[$resolvedPath] = true;
    }

    /**
     * Retrieve an environment variable.
     *
     * Values loaded from environment files take precedence
     * over system environment variables.
     *
     * @param string $key Environment variable name.
     * @param mixed $default Default value.
     *
     * @return mixed
     */
    public static function get(
        string $key,
        mixed $default = null
    ): mixed {
        if (array_key_exists($key, static::$variables)) {
            return static::$variables[$key];
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        $value = getenv($key);

        return $value === false ? $default : $value;
    }

    /**
     * Determine whether an environment variable exists.
     *
     * Checks the loaded environment files and the
     * PHP and system environment variables.
     *
     * @param string $key Environment variable name.
     *
     * @return bool
     */
    public static function has(string $key): bool
    {
        if (array_key_exists($key, static::$variables)) {
            return true;
        }

        return array_key_exists($key, $_ENV)
            || array_key_exists($key, $_SERVER)
            || getenv($key) !== false;
    }
}