<?php

declare(strict_types=1);

namespace Capsule\Http\Middleware;

use Capsule\Contracts\SessionReader;
use Capsule\Contracts\HandlerInterface;
use Capsule\Contracts\MiddlewareInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

/**
 * AuthRequiredMiddleware
 * - Protège un préfixe d’URL (ex: /dashboard)
 * - Laisse passer une whitelist (ex: /login, /logout)
 * - Si non authentifié → 302 vers /login (ou 401 JSON si tu préfères)
 *
 * Invariants:
 * - Zéro I/O (pas de header()/exit)
 * - Ne suppose pas de structure particulière de $_SESSION ; lit via SessionReader
 */
final class AuthRequiredMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly SessionReader $session,
        private readonly string $protectedPrefix = '/dashboard',
        /** @var list<string> */
        private readonly array $whitelist = ['/login','/logout'],
        private readonly string $redirectTo = '/login',
        private readonly string $sessionKey = 'admin' // ex: contient l’utilisateur
    ) {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $path = $request->path; // ta Request a déjà un path normalisé

        // Hors zone protégée → passe
        if (!str_starts_with($path, $this->protectedPrefix)) {
            return $next->handle($request);
        }

        // Whitelist → passe
        if (in_array($path, $this->whitelist, true)) {
            return $next->handle($request);
        }

        // Non authentifié → redirige
        if (!$this->session->has($this->sessionKey)) {
            return Response::redirect($this->redirectTo, 302);
        }

        // Auth OK
        return $next->handle($request);
    }
}
