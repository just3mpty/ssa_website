<?php

declare(strict_types=1);

namespace Capsule\Http\Middleware;

use Capsule\Contracts\SessionReader;
use Capsule\Contracts\HandlerInterface;
use Capsule\Contracts\MiddlewareInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Factory\ResponseFactory as Res;

/**
 * AuthRequiredMiddleware
 * - Protège un préfixe (ex: /dashboard), whitelist configurée.
 * - Non authentifié -> 302 /login (zéro I/O ici).
 */
final class AuthRequiredMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly SessionReader $session,
        private readonly string $protectedPrefix = '/dashboard',
        /** @var list<string> */
        private readonly array $whitelist = ['/login','/logout'],
        private readonly string $redirectTo = '/login',
        private readonly string $sessionKey = 'admin'
    ) {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $path = $request->path;

        if (!str_starts_with($path, $this->protectedPrefix)) {
            return $next->handle($request);
        }
        if (in_array($path, $this->whitelist, true)) {
            return $next->handle($request);
        }
        if (!$this->session->has($this->sessionKey)) {
            return Res::redirect($this->redirectTo, 302);
        }

        return $next->handle($request);
    }
}
