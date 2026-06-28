<?php
declare(strict_types=1);

namespace RedSky\Exceptions;

use RedSky\Http\Response;
use RedSky\Exceptions\HttpException;
use RedSky\Exceptions\ValidationException;
use Throwable;

class Handler
{
    public function handle(Throwable $e): Response
    {
        if ($e instanceof HttpException) {
            return $this->handleHttpException($e);
        }

        if ($e instanceof ValidationException) {
            return $this->handleValidationException($e);
        }

        return $this->handleGenericException($e);
    }

    protected function handleHttpException(HttpException $e): Response
    {
        return Response::json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors'  => $e->getErrors(),
        ], $e->getStatusCode());
    }

    protected function handleValidationException(ValidationException $e): Response
    {
        return Response::json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);
    }

    protected function handleGenericException(Throwable $e): Response
    {
        $debug = config('app.debug');

        return Response::json([
            'success' => false,
            'message' => $debug
                ? $e->getMessage()
                : 'Internal Server Error',
            'exception' => $debug ? [
                'type'  => get_class($e),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ] : null,
        ], 500);
    }

}