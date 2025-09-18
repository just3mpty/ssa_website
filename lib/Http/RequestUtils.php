<?php

declare(strict_types=1);

namespace CapsuleLib\Http;

/**
 * RequestUtils
 * ------------
 * Aides minimales autour de la superglobale $_SERVER.
 *
 * LIMITES: utilitaire de bas niveau, couplé à l’environnement PHP natif.
 *          Pour une application modulaire, préférer PSR-7/PSR-15.
 */
final class RequestUtils
{
    /**
     * @return bool Vrai si la requête HTTP est en POST (strict, sans override)
     */
    public static function isPost(): bool
    {
        // NOTE: volontairement strict; les overrides (X-HTTP-Method-Override) doivent
        // être gérés ailleurs si souhaités.
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /**
     * Convertit un paramètre (string|int ou array shape) en int.
     *
     * ATTENTION:
     *  - Les casts `(int)` silencieux acceptent "12foo" → 12 ; "abc" → 0.
     *  - Utiliser pour cas simples et tolérants; sinon, préférer une fonction stricte (cf. Suggestions).
     */
    public static function intFromParam(string|int|array $param): int
    {
        if (\is_array($param)) {
            // Convention implicite: chercher une clé 'id'. Si absente → 0.
            return (int)($param['id'] ?? 0);
        }
        return (int)$param;
    }

    /**
     * Si la requête n'est pas un POST, effectue une redirection PRG 303 vers $to.
     * Utilitaire de garde simple (continue si POST, sinon never via Redirect::to()).
     *
     * @psalm-return void  // (never en cas de redirection)
     */
    public static function ensurePostOrRedirect(string $to): void
    {
        if (!self::isPost()) {
            Redirect::to($to, 303);
        }
    }
}
