<?php

namespace RedSky\Framework\Routing;

class RouteDefinition
{
    public function __construct(
        protected string $method,
        protected string $uri,
        protected mixed $action,
        protected array $middlewares = [],
        protected ?string $name = null
    ) {}

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function middleware(array|string $middleware): static
    {
        $this->middlewares = array_merge(
            $this->middlewares,
            (array) $middleware
        );

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getAction(): mixed
    {
        return $this->action;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
    
}