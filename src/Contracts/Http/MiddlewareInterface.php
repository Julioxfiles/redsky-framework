<?php
declare(strict_types=1);

namespace Redsky\Framework\Contracts\Http;

use Redsky\Framework\Http\Request;
use Redsky\Framework\Http\Response;
use Closure;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response;
}