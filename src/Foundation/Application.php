<?php

declare(strict_types=1);

namespace RedSky\Framework\Foundation;

use RedSky\Framework\Container\Container;
use RedSky\Framework\Http\Request;
use RedSky\Framework\Http\Response;
use RedSky\Framework\Routing\Route;
use RedSky\Framework\Routing\Router;
use RedSky\Framework\Http\Handler;
use RedSky\Framework\Providers\ServiceProvider;
use RedSky\Framework\Session\Session;

class Application
{
    protected Container $container;
    protected array $providers = [];

    protected static ?self $instance = null;

    protected ?string $routesPath = null;

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
        /*
        |------------------------------------------------------------------
        | SINGLE SOURCE OF TRUTH (Router)
        |------------------------------------------------------------------
        | Creamos UNA sola instancia y la compartimos en todo el sistema
        */

        $router = new Router();

        // 1. Registrar en container como instancia única
        $this->container->instance(Router::class, $router);

        // 2. IMPORTANTE: el facade Route usa esta misma instancia
        Route::setRouter($router);

        // 3. Handler (sin cambios)
        $this->container->singleton(Handler::class, fn () => new Handler());

        $this->container->singleton(
            Session::class,
            fn () => new Session(
                secure: filter_var(
                    env('SESSION_SECURE', true),
                    FILTER_VALIDATE_BOOLEAN
                ),
                httpOnly: true,
                sameSite: 'Lax'
            )
        );

        
    }

    public function loadRoutes(string $path): static
    {
        if (! file_exists($path)) {
            throw new \RuntimeException("Routes file not found: {$path}");
        }

        $this->routesPath = $path;

        return $this;
    }

    /**
     * Handle the current HTTP request.
     *
     * @return void
     */
    public function run(): void
    {
        $sessionEnabled = filter_var(
            env('SESSION_ENABLED', false),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($sessionEnabled) {
            $session = $this->container->make(Session::class);
            $session->start();
        }

        if ($this->routesPath !== null) {
            require $this->routesPath;
        }

        $request = Request::capture();

        // Register the current request in the container.
        $this->container->instance(Request::class, $request);

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

    /**
     * Load development helper functions.
     *
     * @return void
     */
    protected function loadDevTools(): void
    {
        if (env('APP_ENV', 'production') !== 'production') {
            $devPath = dirname(__DIR__, 2) . '/dev/';

            require_once $devPath . 'dev_helpers.php';
        }
    }
   

    public function registerProvider(ServiceProvider $provider): void
    {
        $this->providers[] = $provider;

        $provider->register();
    }

    public function bootProviders(): void
    {
        foreach ($this->providers as $provider) {
            $provider->boot();
        }
    }

}