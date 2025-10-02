<?php

declare(strict_types=1);

namespace Capsule\Http\Middleware;

use Capsule\Contracts\SessionReader;
use Capsule\Contracts\HandlerInterface;
use Capsule\Contracts\MiddlewareInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Contracts\ResponseFactoryInterface;

/**
 * RequiredRoleMiddleware
 * - Vérifie le rôle requis sur le même périmètre.
 */

final class AuthRequiredMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly SessionReader $session,
        private readonly ResponseFactoryInterface $res,
        private readonly string $requiredRole,
        private readonly string $protectedPrefix = '/dashboard',
        /** @var list<string> */
        private readonly array $whitelist = ['/login','/logout'],
        private readonly string $redirectTo = '/login',
        private readonly string $sessionKey = 'admin',
        private readonly string $roleKey = 'role',
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

        $user = $this->session->get($this->sessionKey);
        if (!$user || !\is_array($user) || ($user[$this->roleKey] ?? null) !== $this->requiredRole) {
            // Redirection header-only
            return $this->res->redirect($this->redirectTo, 302);
        }

        return $next->handle($request);
    }
}
