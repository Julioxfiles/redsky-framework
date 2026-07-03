<?php
declare(strict_types=1);

namespace RedSky\Framework\Contracts\Http;

use RedSky\Framework\Http\Request;
use RedSky\Framework\Http\Response;
use Closure;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response;
}