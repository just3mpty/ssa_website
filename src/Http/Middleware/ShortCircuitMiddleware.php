<?php

declare(strict_types=1);

namespace Capsule\Http\Middleware;

use Capsule\Contracts\HandlerInterface;
use Capsule\Contracts\MiddlewareInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

final class ShortCircuitMiddleware implements MiddlewareInterface
{
    public function __construct(private bool $enabled = false)
    {
    }

    public function process(Request $req, HandlerInterface $next): Response
    {
        if ($this->enabled && $req->path === '/maintenance') {
            return Response::text('Service temporarily unavailable', 503);
        }

        return $next->handle($req);
    }
}
