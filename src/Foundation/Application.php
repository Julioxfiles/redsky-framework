<?php

declare(strict_types=1);

namespace RedSky\Framework\Foundation;

use RedSky\Framework\Container\Container;
use RedSky\Framework\Http\Request;
use RedSky\Framework\Http\Response;
use RedSky\Framework\Routing\Route;
use RedSky\Framework\Routing\Router;
use RedSky\Framework\Support\Env;
use RedSky\Framework\Http\Handler;

class Application
{
    protected Container $container;
    protected static ?self $instance = null;

    public function __construct()
    {
        if (static::$instance !== null) {
            return;
        }

        static::$instance = $this;

        $this->container = new Container();

        $this->bootstrap();
        $this->loadDevTools();

        $this->registerCoreServices();
    }

    public static function getInstance(): static
    {
        return static::$instance ??= new static();
    }

    public function container(): Container
    {
        return $this->container;
    }

    protected function registerCoreServices(): void
    {
        $this->container->singleton(Router::class, fn () => new Router());

        $this->container->singleton(Handler::class, fn () => new Handler());
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
    }

    protected function loadDevTools(): void
    {
        if (($_ENV['APP_ENV'] ?? 'production') !== 'production') {
            require dirname(__DIR__, 2) . '/src/Support/helpers.php';
        }
    }

}