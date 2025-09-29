<?php

declare(strict_types=1);

namespace Capsule\Http\Middleware;

use Capsule\Contracts\HandlerInterface;
use Capsule\Contracts\MiddlewareInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

/**
 * SecurityHeaders
 * - Ajoute des entêtes de sécurité par défaut si absents.
 * - Idempotent : ne remplace pas les entêtes déjà définis.
 */
final class SecurityHeaders implements MiddlewareInterface
{
    public function __construct(
        private readonly string $csp = "default-src 'none'; frame-ancestors 'none'; base-uri 'none'"
    ) {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $res = $next->handle($request);

        if (!$res->hasHeader('X-Content-Type-Options')) {
            $res = $res->withHeader('X-Content-Type-Options', 'nosniff');
        }
        if (!$res->hasHeader('Referrer-Policy')) {
            $res = $res->withHeader('Referrer-Policy', 'no-referrer');
        }
        if (!$res->hasHeader('X-Frame-Options')) {
            $res = $res->withHeader('X-Frame-Options', 'DENY');
        }
        if (!$res->hasHeader('Content-Security-Policy')) {
            $res = $res->withHeader('Content-Security-Policy', $this->csp);
        }
        // Bonus en HTTPS : Strict-Transport-Security
        // if (!$res->hasHeader('Strict-Transport-Security')) {
        //     $res = $res->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        // }

        return $res;
    }
}
