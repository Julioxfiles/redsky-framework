<?php

namespace RedSky\Framework\Http;

use Throwable;

class Handler
{
    /**
     * Convert any exception into a Response.
     */
    public function handle(Throwable $e): Response
    {
        // Default status code
        $status = $this->resolveStatusCode($e);

        return Response::json([
            'success' => false,
            'status'  => $status,
            'message' => $e->getMessage(),
            'exception' => $this->shouldExposeDetails()
                ? [
                    'type'  => get_class($e),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ]
                : null,
        ], $status);
    }

    /**
     * Map exception types to HTTP status codes.
     */
    protected function resolveStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof \InvalidArgumentException => 400,
            $e instanceof \RuntimeException         => 500,
            $e instanceof \LogicException           => 500,
            default                                 => 500,
        };
    }

    /**
     * Control whether debug details are exposed.
     * (Later this should come from config)
     */
    protected function shouldExposeDetails(): bool
    {
        return true; // for dev mode (later we link to config)
    }
    
}