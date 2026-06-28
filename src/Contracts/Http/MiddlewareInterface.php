<?php
declare(strict_types=1);

namespace RedSky\Contracts\Http;

use RedSky\Http\Request;
use RedSky\Http\Response;
use Closure;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response;
}