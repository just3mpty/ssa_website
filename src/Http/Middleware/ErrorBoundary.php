<?php

declare(strict_types=1);

namespace Capsule\Http\Middleware;

use Capsule\Contracts\HandlerInterface;
use Capsule\Contracts\MiddlewareInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Factory\ResponseFactory as Res;

/**
 * ErrorBoundary
 * - Capture les exceptions et les mappe en réponses HTTP propres.
 * - En dev (debug=true), inclut message/trace.
 * - En prod, renvoie une réponse générique 500.
 */
final class ErrorBoundary implements MiddlewareInterface
{
    public function __construct(private readonly bool $debug = false)
    {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        try {
            return $next->handle($request);
        } catch (\Capsule\Routing\NotFound $e) {
            return Res::json(['error' => 'Not Found'], 404);
        } catch (\Throwable $e) {
            if ($this->debug) {
                return Res::json([
                    'error' => 'Exception',
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                    'trace' => explode("\n", $e->getTraceAsString()),
                ], 500);
            }

            return Res::json(['error' => 'Server Error'], 500);
        }
    }
}
