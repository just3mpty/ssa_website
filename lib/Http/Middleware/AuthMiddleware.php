<?php
#
declare(strict_types=1);

#te
namespace CapsuleLib\Http\Middleware;

use CapsuleLib\Security\Authenticator;

/**
 * Middleware d’authentification : bloque l’accès si non connecté.
 *
 * À inclure en haut de chaque contrôleur/route sensible :
 *     AuthMiddleware::handle();
 */
class AuthMiddleware
{
    /**
     * Bloque l’accès si l’utilisateur n’est pas authentifié (admin).
     * Redirige vers /login.
     *
     * @return void
     */
    public static function handle(): void
    {
        // S’assurer que la session est démarrée
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Si non authentifié, redirige vers login
        if (!Authenticator::isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        // Sinon, l’exécution continue
    }

    public static function requireRole(string $role): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $user = Authenticator::getUser();
        if (!$user || ($user['role'] ?? null) !== $role) {
            header('Location: /login');
            exit;
        }
    }
}
