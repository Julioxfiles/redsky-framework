<?php

namespace RedSky\Framework\Http;

class Response
{
    protected int $status;
    protected array $headers = [];
    protected mixed $body;

    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    /* =========================================================
     | Constructor
     |========================================================= */

    public function __construct(
        mixed $body = null,
        int $status = 200,
        array $headers = []
    ) {
        $this->body    = $body;
        $this->status  = $status;
        $this->headers = $headers;
    }

    /* =========================================================
     | Factory methods
     |========================================================= */

    public static function make(
        mixed $body = null,
        int $status = 200,
        array $headers = []
    ): static {
        return new static($body, $status, $headers);
    }

    public static function json(
        mixed $data,
        int $status = 200,
        array $headers = []
    ): static {
        $headers['Content-Type'] = 'application/json';

        return new static(
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            $status,
            $headers
        );
    }

    public static function text(
        string $content,
        int $status = 200,
        array $headers = []
    ): static {
        $headers['Content-Type'] = 'text/plain';

        return new static($content, $status, $headers);
    }

    public static function noContent(): static
    {
        return new static(null, 204);
    }

    /* =========================================================
     | Status
     |========================================================= */

    public function status(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    /* =========================================================
     | Body
     |========================================================= */

    public function body(): mixed
    {
        return $this->body;
    }

    public function setBody(mixed $body): static
    {
        $this->body = $body;
        return $this;
    }

    /* =========================================================
     | Headers
     |========================================================= */

    public function headers(): array
    {
        return $this->headers;
    }

    public function setHeader(string $key, string $value): static
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setHeaders(array $headers): static
    {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }

        return $this;
    }

    /* =========================================================
     | Convenience headers
     |========================================================= */

    public function withHeader(string $key, string $value): static
    {
        return $this->setHeader($key, $value);
    }

    public function withHeaders(array $headers): static
    {
        return $this->setHeaders($headers);
    }

    public function withJson(): static
    {
        return $this->setHeader('Content-Type', 'application/json');
    }

    public function withText(): static
    {
        return $this->setHeader('Content-Type', 'text/plain');
    }

    public function withLocation(string $url): static
    {
        return $this->setHeader('Location', $url);
    }

    public function withCors(
        string $origin = '*',
        string $methods = 'GET,POST,PUT,PATCH,DELETE,OPTIONS',
        string $headers = 'Content-Type, Authorization'
    ): static {
        $this->headers['Access-Control-Allow-Origin']  = $origin;
        $this->headers['Access-Control-Allow-Methods'] = $methods;
        $this->headers['Access-Control-Allow-Headers'] = $headers;

        return $this;
    }

    /* =========================================================
     | Getter helpers
     |========================================================= */

    public function isJson(): bool
    {
        return ($this->headers['Content-Type'] ?? '') === 'application/json';
    }

    public function isOk(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function isError(): bool
    {
        return $this->status >= 400;
    }

    public static function ok(mixed $data = null, string $message = 'OK'): static
    {
        return static::json([
            'success' => true,
            'status'  => 200,
            'message' => $message,
            'data'    => $data,
            'errors'  => [],
        ], 200);
    }

    public static function created(mixed $data = null, string $message = 'Created'): static
    {
        return static::json([
            'success' => true,
            'status'  => 201,
            'message' => $message,
            'data'    => $data,
            'errors'  => [],
        ], 201);
    }
        
    public static function error(string $message, int $status = 400, array $errors = []): static
    {
        return static::json([
            'success' => false,
            'status'  => $status,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $status);
    }

    public static function validationError(array $errors, string $message = 'Validation failed'): static
    {
        return static::error($message, 422, $errors);
    }

    public static function redirect(string $to, int $status = 302): static
    {
        return new static(null, $status, [
            'Location' => $to,
        ]);
    }

}
