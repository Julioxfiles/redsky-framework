<?php

// redsky-framework/src/Support/helpers.php

use RedSky\Framework\Foundation\Application;
use RedSky\Framework\Http\Response;

if (!function_exists('app')) {
    function app(): Application
    {
        return Application::getInstance();
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value =
            $_ENV[$key]
            ?? $_SERVER[$key]
            ?? getenv($key);

        return ($value === false || $value === null)
            ? $default
            : $value;
    }
}

if (!function_exists('uuid')) {
    function uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $config = null;

        if ($config === null) {
            $config = [];

            $basePath = defined('BASE_PATH')
                ? BASE_PATH
                : getcwd();

            foreach (glob($basePath . '/config/*.php') as $file) {
                $name = basename($file, '.php');
                $config[$name] = require $file;
            }
        }

        $segments = explode('.', $key);
        $value = $config;

        foreach ($segments as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('random_str')) {
    function random_str(int $length = 12): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = defined('BASE_PATH')
            ? BASE_PATH
            : getcwd();

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('class_basename')) {
    function class_basename(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (! function_exists('asset')) {

    /**
     * Generate the URL for a public asset.
     */
    function asset(string $path = ''): string
    {
        $path = ltrim($path, '/');

        return '/redsky/redsky-ui/public/' . $path;
    }
}
