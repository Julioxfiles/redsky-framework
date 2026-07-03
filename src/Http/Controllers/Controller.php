<?php
declare(strict_types=1);

namespace RedSky\Framework\Http\Controllers;

use RedSky\Framework\Http\Request;
use RedSky\Framework\Http\Response;
use Throwable;

abstract class Controller
{
    protected Request $request;
    protected array $meta = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /* =========================================================
     | SUCCESS RESPONSES
     |========================================================= */

    protected function success(mixed $data = null, string $message = 'OK'): Response
    {
        return Response::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $this->meta,
        ], Response::HTTP_OK ?? 200);
    }

    protected function created(mixed $data = null, string $message = 'Created'): Response
    {
        return Response::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $this->meta,
        ], Response::HTTP_CREATED ?? 201);
    }

    protected function noContent(): Response
    {
        return Response::noContent();
    }

    /* =========================================================
     | ERROR RESPONSES
     |========================================================= */

    protected function error(string $message, int $status = 400): Response
    {
        return Response::json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => [],
        ], $status);
    }

    protected function badRequest(string $message = 'Bad Request'): Response
    {
        return $this->error($message, 400);
    }

    protected function unauthorized(string $message = 'Unauthorized'): Response
    {
        return $this->error($message, 401);
    }

    protected function forbidden(string $message = 'Forbidden'): Response
    {
        return $this->error($message, 403);
    }

    protected function notFound(string $message = 'Not Found'): Response
    {
        return $this->error($message, 404);
    }

    protected function validationError(array $errors): Response
    {
        return Response::json([
            'success' => false,
            'message' => 'Validation failed',
            'data'    => null,
            'errors'  => $errors,
        ], 422);
    }

    protected function serverError(
        string $message = 'Internal Server Error',
        ?Throwable $exception = null
    ): Response {
        $debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return Response::json([
            'success' => false,
            'message' => $debug && $exception
                ? $exception->getMessage()
                : $message,
            'data'    => null,
            'errors'  => [],
            'exception' => $debug && $exception
                ? [
                    'type' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]
                : null,
        ], 500);
    }

    /* =========================================================
     | REQUEST HELPERS
     |========================================================= */

    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    protected function all(): array
    {
        return $this->request->all();
    }

    protected function only(array $keys): array
    {
        return $this->request->only($keys);
    }

    protected function except(array $keys): array
    {
        return $this->request->except($keys);
    }

    protected function query(string $key, mixed $default = null): mixed
    {
        return $this->request->query($key, $default);
    }

    protected function header(string $key, mixed $default = null): mixed
    {
        return $this->request->header($key, $default);
    }

    /* =========================================================
     | PAGINATION
     |========================================================= */

    protected function paginate(
        array $items,
        int $total,
        int $perPage,
        int $page
    ): array {
        $lastPage = (int) ceil($total / $perPage);

        $this->meta['pagination'] = [
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $lastPage,
        ];

        return $items;
    }

    protected function page(): int
    {
        return max(1, (int) $this->query('page', 1));
    }

    protected function perPage(int $default = 15): int
    {
        return (int) $this->query('per_page', $default);
    }

    /* =========================================================
     | SORT / FILTER
     |========================================================= */

    protected function sort(): ?string
    {
        return $this->query('sort');
    }

    protected function direction(): string
    {
        return strtolower($this->query('direction', 'asc')) === 'desc'
            ? 'desc'
            : 'asc';
    }

    protected function filters(): array
    {
        return (array) $this->query('filter', []);
    }

    /* =========================================================
     | LIFECYCLE
     |========================================================= */

    protected function before(): void {}

    protected function after(Response $response): Response
    {
        return $response;
    }

    /* =========================================================
     | SAFE EXECUTOR
     |========================================================= */

    protected function execute(callable $callback): Response
    {
        try {
            $this->before();

            $response = $callback();

            if (!$response instanceof Response) {
                $response = $this->success($response);
            }

            return $this->after($response);

        } catch (Throwable $e) {
            return $this->serverError('Internal Controller Error', $e);
        }
    }
}