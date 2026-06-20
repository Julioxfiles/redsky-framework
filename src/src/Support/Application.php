<?php

namespace Redsky\Framework\Support;

use Redsky\Framework\Container\Container;

class Application
{
    protected static ?Application $instance = null;

    protected Container $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public static function getInstance(): Application
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function make(string $abstract)
    {
        return $this->container->make($abstract);
    }

    public function bind(string $abstract, $concrete)
    {
        $this->container->bind($abstract, $concrete);
    }

    public function singleton(string $abstract, $concrete)
    {
        $this->container->singleton($abstract, $concrete);
    }
    
}