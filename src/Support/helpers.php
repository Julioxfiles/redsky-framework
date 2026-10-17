<?php

// redsky-framework/src/Support/helpers.php

use RedSky\Framework\Foundation\Application;

if (!function_exists('app')) {
    /**
     * Retrieve the application or resolve a service.
     *
     * @param string|null $abstract Service class or identifier.
     *
     * @return mixed
     */
    function app(?string $abstract = null): mixed
    {
        $app = Application::getInstance();

        if ($abstract === null) {
            return $app;
        }

        return $app->container()->make($abstract);
    }
}

if (! function_exists('env')) {
    /**
     * Retrieve an environment variable.
     *
     * @param string $key     Environment variable name.
     * @param mixed  $default Default value when the variable is unavailable.
     *
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        return \RedSky\Framework\Support\Env::get($key, $default);
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


/*
|--------------------------------------------------------------------------
| Request Helper
|--------------------------------------------------------------------------
*/

if (!function_exists('request')) {
    /**
     * Retrieve the current HTTP request.
     *
     * @return \RedSky\Framework\Http\Request
     */
    function request(): \RedSky\Framework\Http\Request
    {
        return app()->container()->make(
            \RedSky\Framework\Http\Request::class
        );
    }
}

/*
|--------------------------------------------------------------------------
| Response Helper
|--------------------------------------------------------------------------
*/

if (!function_exists('response')) {
    /**
     * Retrieve the response factory.
     *
     * @return \RedSky\Framework\Http\ResponseFactory
     */
    function response(): \RedSky\Framework\Http\ResponseFactory
    {
        return new \RedSky\Framework\Http\ResponseFactory();
    }
}

/*
|--------------------------------------------------------------------------
| Redirect Helper
|--------------------------------------------------------------------------
*/

if (!function_exists('redirect')) {
    function redirect(string $to): \RedSky\Framework\Http\Response
    {
        return response()->redirect($to);
    }
}

/*
|--------------------------------------------------------------------------
| URL Helper (simple base)
|--------------------------------------------------------------------------
*/

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(env('APP_URL', 'http://localhost'), '/');

        return $base . '/' . ltrim($path, '/');
    }
}

