<?php

namespace Redsky\Framework\Http;

use Redsky\Framework\Container\Container;

class Kernel
{
    protected Container $container;

    protected array $middleware = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Bootstrap principal del framework
     */
    public function handle($request)
    {
        // 1. Ejecutar middleware global (placeholder)
        $request = $this->runMiddleware($request);

        // 2. Resolver respuesta base (placeholder)
        return $this->dispatch($request);
    }

    /**
     * Middleware pipeline (versión inicial simple)
     */
    protected function runMiddleware($request)
    {
        foreach ($this->middleware as $middleware) {

            if (is_string($middleware)) {
                $middleware = $this->container->make($middleware);
            }

            if (method_exists($middleware, 'handle')) {
                $request = $middleware->handle($request);
            }
        }

        return $request;
    }

    /**
     * Punto de salida del request
     * (después conectaremos Router)
     */
    protected function dispatch($request)
    {
        return $request;
    }

    /**
     * Agregar middleware global
     */
    public function addMiddleware($middleware)
    {
        $this->middleware[] = $middleware;
    }
}