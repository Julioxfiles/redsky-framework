<?php

namespace RedSky\Http;

class Request
{
    protected string $method;
    protected string $uri;
    protected string $path;

    protected array $query = [];
    protected array $body = [];
    protected array $headers = [];
    protected array $cookies = [];
    protected array $files = [];
    protected array $server = [];

    /* =========================================================
     | Factory
     |========================================================= */

    public static function capture(): static
    {
        return new static(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
            $_GET,
            $_POST,
            getallheaders() ?: [],
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
    }

    /* =========================================================
     | Constructor
     |========================================================= */

    public function __construct(
        string $method,
        string $uri,
        array $query = [],
        array $body = [],
        array $headers = [],
        array $cookies = [],
        array $files = [],
        array $server = []
    ) {
        $this->method  = strtoupper($method);
        $this->uri     = $uri;
        $this->path    = $this->resolvePath($uri);

        $this->query   = $query;
        $this->body    = $body;
        $this->headers = array_change_key_case($headers, CASE_LOWER);
        $this->cookies = $cookies;
        $this->files   = $files;
        $this->server  = $server;
    }

    /* =========================================================
     | Core getters
     |========================================================= */

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->path();
    }

    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        $script = dirname($_SERVER['SCRIPT_NAME']);

        if ($script !== '/' && str_starts_with($path, $script)) {
            $path = substr($path, strlen($script));
        }

        return $path ?: '/';
    }

    public function query(): array
    {
        return $this->query;
    }

    public function body(): array
    {
        return $this->body;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function cookies(): array
    {
        return $this->cookies;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function server(): array
    {
        return $this->server;
    }

    /* =========================================================
     | Path resolver (framework-safe)
     |========================================================= */

    protected function resolvePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        return '/' . trim($path, '/');
    }

    /* =========================================================
     | Helpers básicos
     |========================================================= */

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }

    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    /* =========================================================
     | Input access (simple unified layer)
     |========================================================= */

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function only(array $keys): array
    {
        return array_intersect_key(
            $this->all(),
            array_flip($keys)
        );
    }

    public function except(array $keys): array
    {
        return array_diff_key(
            $this->all(),
            array_flip($keys)
        );
    }

    /* =========================================================
     | Headers helpers
     |========================================================= */

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function hasHeader(string $key): bool
    {
        return isset($this->headers[strtolower($key)]);
    }

    /* =========================================================
     | Segments
     |========================================================= */

    public function segments(): array
    {
        return $this->path === '/'
            ? []
            : explode('/', trim($this->path, '/'));
    }

    public function segment(int $index, mixed $default = null): mixed
    {
        return $this->segments()[$index - 1] ?? $default;
    
    }

    public function filled(string $key): bool
    {
        if (! $this->has($key)) {
            return false;
        }

        $value = $this->input($key);

        return !(
            $value === null ||
            $value === '' ||
            $value === [] ||
            (is_string($value) && trim($value) === '')
        );
    }
 
    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);

        if (is_array($value) || is_object($value)) {
            return $default;
        }

        return trim((string) $value);
    }

    public function integer(string $key, int $default = 0): int
    {
        $value = $this->input($key, $default);

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    public function boolean(string $key, bool $default = false): bool
    {
        if (! $this->has($key)) {
            return $default;
        }

        $value = $this->input($key);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            ?? $default;
    }

    public function array(string $key, array $default = []): array
    {
        $value = $this->input($key, $default);

        return is_array($value) ? $value : $default;
    }

    public function missing(string $key): bool
    {
        return ! $this->has($key);
    }

}