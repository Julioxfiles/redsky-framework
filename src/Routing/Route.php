<?php

namespace RedSky\Routing;

use RedSky\Routing\Router;

class Route
{
    protected static Router $router;

    public static function setRouter(Router $router): void
    {
        static::$router = $router;
    }

    public static function get(string $uri, callable|array $action): RouteDefinition
    {
        return static::$router->add('GET', $uri, $action);
    }

    public static function post(string $uri, callable|array $action): RouteDefinition
    {
        return static::$router->add('POST', $uri, $action);
    }

    public static function put(string $uri, callable|array $action): RouteDefinition
    {
        return static::$router->add('PUT', $uri, $action);
    }

    public static function patch(string $uri, callable|array $action): RouteDefinition
    {
        return static::$router->add('PATCH', $uri, $action);
    }

    public static function delete(string $uri, callable|array $action): RouteDefinition
    {
        return static::$router->add('DELETE', $uri, $action);
    }

    public static function resource(string $name, string $controller): void
    {
        $base = '/' . trim($name, '/');

        static::get($base, [$controller, 'index'])->name("$name.index");
        static::post($base, [$controller, 'store'])->name("$name.store");
        static::get("$base/{id}", [$controller, 'show'])->name("$name.show");
        static::put("$base/{id}", [$controller, 'update'])->name("$name.update");
        static::delete("$base/{id}", [$controller, 'destroy'])->name("$name.destroy");
    }
    
}