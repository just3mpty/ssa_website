<?php

declare(strict_types=1);

namespace CapsuleLib\Middleware;

use CapsuleLib\Security\CurrentUserProvider;

/**
 * Middleware d’authentification.
 *
 * Garantit que l’utilisateur est connecté avant d’accéder à une route ou un contrôleur protégé.
 * Utiliser au début de chaque contrôleur ou route nécessitant une authentification.
 *
 * Exemple d’usage :
 * ```
 * AuthMiddleware::handle();
 * ```
 */
class AuthMiddleware
{
    /**
     * Vérifie que l’utilisateur est authentifié.
     * 
     * Si non authentifié, redirige vers la page de connexion (/login) et stoppe l’exécution.
     *
     * @return void
     */
    public static function handle(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!CurrentUserProvider::isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Vérifie que l’utilisateur a un rôle spécifique.
     *
     * Si non connecté ou rôle non autorisé, redirige vers /login et stoppe l’exécution.
     *
     * @param string $role Rôle requis (ex : 'admin')
     * @return void
     */
    public static function requireRole(string $role): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $user = CurrentUserProvider::getUser();

        if (!$user || ($user['role'] ?? null) !== $role) {
            header('Location: /login');
            exit;
        }
    }
}
