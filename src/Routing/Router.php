<?php

namespace RedSky\Framework\Routing;

use Closure;
use Exception;
use RedSky\Framework\Routing\RouteDefinition;
use RedSky\Framework\Http\Request;
use RedSky\Framework\Http\Response;

class Router
{
    protected array $routes = [];
    protected array $groupStack = [];
    protected array $namedRoutes = [];
    protected array $middlewareAliases = [];
    protected array $middlewareGroups  = [];
    protected array $globalMiddleware  = [];

    /* =========================
       HTTP verbs
    ========================== */

    public function get(string $uri, callable|array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, callable|array $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, callable|array $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function patch(string $uri, callable|array $action): void
    {
        $this->addRoute('PATCH', $uri, $action);
    }

    public function delete(string $uri, callable|array $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    /* =========================
       Route groups
    ========================== */

    public function group(array $attributes, Closure $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    /* =========================
       Dispatch
    ========================== */

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        //$uri    = $this->normalizeUri($request->path());
        $uri = $request->path();
        
        //var_dump($uri);
        //echo "<pre>";
        //print_r($this->routes);
        //die();

        foreach ($this->routes[$method] ?? [] as $route) {

            $params = [];

            if ($this->match($route->getUri(), $uri, $params)) {

                $request->setRouteParameters($params);

                $controller = function (Request $request) use ($route, $params) {
                    return $this->execute(
                        $route->getAction(),
                        $params,
                        $request
                    );
                };

                $response = $this->runMiddleware(
                    $this->resolveMiddlewares($route->getMiddlewares()),
                    $request,
                    $controller
                );

                // 🔥 Asegura que SIEMPRE sea Response
                return $this->normalizeResponse($response);
            }
        }

        return Response::json([
            'success' => false,
            'message' => 'Route not found',
            'status'  => 404
        ], 404);
    }
    /* =========================
       Internals
    ========================== */

    protected function addRoute(
        string $method,
        string $uri,
        callable|array $action
    ): RouteDefinition {
        $definition = new RouteDefinition(
            $method,
            $this->normalizeUri($this->applyGroupPrefix($uri)),
            $action
        );

        $this->routes[$method][] = $definition;

        return $definition;
    }

    public function add(string $method, string $uri, callable|array $action): RouteDefinition
    {
        return $this->addRoute($method, $uri, $action);
    }

    protected function execute(
        callable|array $action,
        array $params,
        Request $request
    ): Response {
        // Closure
        if ($action instanceof Closure) {
            $result = $action($request, ...$params);
            return $this->normalizeResponse($result);
        }

        // Controller
        if (is_array($action)) {
            return $this->callController(
                $action,
                $params,
                $request
            );
        }

        throw new Exception('Invalid route action');
    }

    protected function callController(
        array $action,
        array $params,
        Request $request
    ): Response {
        [$controllerClass, $method] = $action;
        //var_dump($controllerClass);
        //var_dump($action);
        //die();

        // 🔥 Ya NO se inyecta Response
        $controller = new $controllerClass($request);

        $ref = new \ReflectionMethod($controllerClass, $method);
        $args = [];

        foreach ($ref->getParameters() as $param) {
            $type = $param->getType();

            if ($type) {
                $typeName = $type instanceof \ReflectionNamedType
                    ? $type->getName()
                    : null;

                if ($typeName === Request::class) {
                    $args[] = $request;
                } else {
                    $args[] = array_shift($params);
                }
            } else {
                $args[] = array_shift($params);
            }
        }

        $result = $ref->invokeArgs($controller, $args);

        return $this->normalizeResponse($result);
    }

    protected function normalizeResponse(mixed $result): Response
    {
        // 1. Already a Response instance
        if ($result instanceof Response) {
            return $result;
        }

        // 2. null → 204
        if ($result === null) {
            return Response::noContent();
        }

        // 3. String → text response (Laravel behavior)
        if (is_string($result)) {
            return Response::text($result);
        }

        // 4. Array → JSON response
        if (is_array($result)) {
            return Response::json($result, 200);
        }

        // 5. Scalar (int/bool/float)
        if (is_scalar($result)) {
            return Response::json(['data' => $result], 200);
        }

        // 6. Objects → attempt serialization
        if (is_object($result)) {
            if (method_exists($result, 'toArray')) {
                return Response::json($result->toArray(), 200);
            }

            return Response::json(get_object_vars($result), 200);
        }

        // 7. Fallback safety
        return Response::json([
            'message' => 'Unsupported response type'
        ], 500);
    }

    protected function match(string $routeUri, string $requestUri, &$params): bool
    {
        $routeParts   = explode('/', trim($routeUri, '/'));
        $requestParts = explode('/', trim($requestUri, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        $params = [];

        foreach ($routeParts as $index => $part) {

            if (preg_match('/^{(.+)}$/', $part, $matches)) {

                // $matches[1] contiene el nombre del parámetro
                $params[$matches[1]] = $requestParts[$index];

                continue;
            }

            if ($part !== $requestParts[$index]) {
                return false;
            }
        }

        return true;
    }

    protected function applyGroupPrefix(string $uri): string
    {
        $prefix = '';

        foreach ($this->groupStack as $group) {
            if (!empty($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }

        return trim($prefix . '/' . trim($uri, '/'), '/');
    }

    protected function normalizeUri(string $uri): string
    {
        return '/' . trim($uri, '/');
    }

    public static function getByName(string $name): RouteDefinition
    {
        return static::$namedRoutes[$name];
    }

    protected function runMiddleware(
        array $middlewares,
        Request $request,
        Closure $controller
    ): Response {
        return array_reduce(
            array_reverse($middlewares),
            fn ($next, $middleware) => fn ($req) =>
                (new $middleware)->handle($req, $next),
            $controller
        )($request);
    }

    public function aliasMiddleware(string $alias, string $class): void
    {
        $this->middlewareAliases[$alias] = $class;
    }

    protected function resolveMiddlewares(array $routeMiddlewares): array
    {
        $resolved = [];

        // Global
        foreach ($this->globalMiddleware as $middleware) {
            $resolved[] = $middleware;
        }

        // Route
        foreach ($routeMiddlewares as $middleware) {

            // Grupo
            if (isset($this->middlewareGroups[$middleware])) {
                foreach ($this->middlewareGroups[$middleware] as $groupMiddleware) {
                    $resolved[] = $this->middlewareAliases[$groupMiddleware]
                        ?? $groupMiddleware;
                }
                continue;
            }

            // Alias
            if (isset($this->middlewareAliases[$middleware])) {
                $resolved[] = $this->middlewareAliases[$middleware];
                continue;
            }

            // Directo
            $resolved[] = $middleware;
        }

        return $resolved;
    }

    public function middlewareGroup(string $name, array $middlewares): void
    {
        $this->middlewareGroups[$name] = $middlewares;
    }

    public function pushGlobalMiddleware(string $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }
    
}