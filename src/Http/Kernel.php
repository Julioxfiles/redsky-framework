<?php

namespace RedSky\Framework\Http;

use Throwable;
use RedSky\Framework\Container\Container;

class Kernel
{
    public function __construct(
        protected Container $container,
        protected Router $router,
        protected Handler $handler
    ) {}

    /**
     * MAIN ENTRY POINT
     */
    public function handle(Request $request): Response
    {
        try {
            // Router is the ONLY lifecycle owner
            return $this->router->dispatch($request);

        } catch (Throwable $e) {
            return $this->handler->handle($e);
        }
    }
    
}