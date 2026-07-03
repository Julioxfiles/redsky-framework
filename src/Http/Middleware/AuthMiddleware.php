<?php
declare(strict_types=1);

namespace RedSky\Franework\Http\Middleware;

use RedSky\Framework\Contracts\Http\MiddlewareInterface;
use RedSky\Framework\Http\Request;
use RedSky\Framework\Http\Response;
use RedSky\Framework\Security\Jwt\JwtService;
use Closure;

class AuthMiddleware implements MiddlewareInterface
{
    protected JwtService $jwt;

    public function __construct()
    {
        $this->jwt = new JwtService(env('JWT_SECRET'));
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return $this->unauthorized('Authorization token missing');
        }

        if (!$this->jwt->verify($token)) {
            return $this->unauthorized('Invalid or expired token');
        }

        $claims = $this->jwt->decode($token);

        if (method_exists($request, 'setUser')) {
            $request->setUser($claims);
        }

        return $next($request);
    }

    /* ========================================
     | Helpers
     |======================================== */

    protected function extractToken(Request $request): ?string
    {
        if (!$request->hasHeader('Authorization')) {
            return null;
        }

        $header = $request->header('Authorization');

        if (!str_starts_with($header, 'Bearer ')) {
            return null;
        }

        return trim(substr($header, 7));
    }

    /* ========================================
     | Response helpers (framework-safe)
     |======================================== */

    protected function unauthorized(string $message): Response
    {
        return Response::json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => [],
        ], 401);
    }
    
}