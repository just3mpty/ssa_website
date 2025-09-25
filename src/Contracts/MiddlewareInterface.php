<?php

declare(strict_types=1);

namespace Capsule\Contracts;

use Capsule\Http\Request;
use Capsule\Http\Response;

/**
 * Middleware single-pass.
 *
 * Contrats :
 * - Appeler $next($request) au plus une fois.
 * - Retourner une Response (toujours).
 *
 * @param callable(Request):Response $next
 */
interface MiddlewareInterface
{
    /**
     * @param callable(): mixed $next
     */
    public function process(Request $request, callable $next): Response;
}
