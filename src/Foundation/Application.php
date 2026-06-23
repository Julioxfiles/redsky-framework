<?php

namespace Redsky\Framework\Foundation;

use Redsky\Framework\Container\Container;
use Redsky\Framework\Http\Request;
use Redsky\Framework\Http\Response;
use Redsky\Framework\Routing\Route;
use Redsky\Framework\Routing\Router;
use Redsky\Framework\Support\Env;
use Redsky\Framework\Http\Handler;

class Application
{
    protected Container $container;
    protected static ?self $instance = null;

    public function __construct()
    {
        $this->container = new Container();

        $this->bootstrap();
        $this->loadDevTools();

        /**
         * Router MUST come from bootstrap/app.php
         * (single source of truth)
         */
        $this->container->singleton(Router::class, fn () => new Router());

        $this->container->singleton(Handler::class, fn () => new Handler());
    }

    public static function getInstance(): static
    {
        return static::$instance ??= new static();
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function run(): void
    {
        $request = Request::capture();

        $router = $this->container->make(Router::class);

        $response = $router->dispatch($request);

        $this->send($response);
    }

    protected function send(Response $response): void
    {
        http_response_code($response->status());

        foreach ($response->headers() as $key => $value) {
            header("$key: $value");
        }

        echo $response->body();
    }

    protected function bootstrap(): void
    {
        $this->bootEnvironment();
    }

    protected function bootEnvironment(): void
    {
        $envPath = dirname(__DIR__, 3) . '/.env';

        if (file_exists($envPath)) {
            Env::load($envPath);
        }
    }

    protected function loadDevTools(): void
    {
        if (($_ENV['APP_ENV'] ?? 'production') !== 'production') {
            require dirname(__DIR__, 2) . '/dev/helpers.php';
        }
    }
    
}