<?php

namespace Redsky\Framework\Foundation;

use Redsky\Framework\Container\Container;
use Redsky\Framework\Http\Kernel;
use Redsky\Framework\Http\Request;
use Redsky\Framework\Http\Response;
use Redsky\Framework\Http\Router;
use Redsky\Framework\Http\Handler;

class Application
{
    protected Container $container;
    protected Kernel $kernel;
    protected static ?self $instance = null;

    public function __construct()
    {
        $this->container = new Container();

        $router  = new Router();
        $handler = new Handler();

        $this->kernel = new Kernel(
            $this->container,
            $router,
            $handler
        );

        // IMPORTANT: bind router to Route facade
        \Redsky\Framework\Http\Route::setRouter($router);
    }

    public static function getInstance(): static
    {
        return static::$instance ??= new static();
    }

    public function kernel(): Kernel
    {
        return $this->kernel;
    }

    public function run(): void
    {
        $request  = Request::capture();
        $response = $this->kernel->handle($request);

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

}