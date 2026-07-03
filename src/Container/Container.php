<?php

namespace RedSky\Framework\Container;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = function ($container) use ($concrete, $abstract) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $container->resolve($concrete);
            }

            return $this->instances[$abstract];
        };
    }

    public function make(string $abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract];

            if ($concrete instanceof \Closure) {
                return $concrete($this);
            }

            return $this->resolve($concrete);
        }

        return $this->resolve($abstract);
    }

    protected function resolve(string|callable $concrete)
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this);
        }

        return $this->build($concrete);
    }

    protected function build(string $concrete)
    {
        if (!class_exists($concrete)) {
            throw new \Exception("Class {$concrete} not found");
        }

        $reflector = new \ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (!$constructor) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();

        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (!$type) {
                throw new \Exception("Cannot resolve parameter {$parameter->getName()}");
            }

            $dependencies[] = $this->make($type->getName());
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

}