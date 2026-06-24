<?php
declare(strict_types=1);

namespace RedSky\Framework\Http\Middleware;

use RedSky\Framework\Http\Contracts\Middleware;
use RedSky\Framework\Http\Request;
use RedSky\Framework\Http\Response;
use Closure;

class JsonMiddleware implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * 1️⃣ El cliente DEBE aceptar JSON
         */
        if (!$request->expectsJson()) {
            return Response::error(
                'This API only supports JSON responses',
                406
            );
        }

        /**
         * 2️⃣ Si hay body, debe ser application/json
         */
        if ($request->hasBody() && !$request->isJson()) {
            return Response::error(
                'Content-Type must be application/json',
                415
            );
        }

        /**
         * 3️⃣ Ejecutar siguiente middleware / controller
         */
        $response = $next($request);

        /**
         * 4️⃣ Forzar response JSON
         */
        return $response->setHeader('Content-Type', 'application/json');
    }
}