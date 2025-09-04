<?php

declare(strict_types=1);

namespace CapsuleLib\Security;

final class CurrentUserProvider
{
    /**
     * Retourne l'utilisateur courant depuis la session.
     *
     * @return array{ id?: int, username?: string, role?: string }|null
     */
    public static function getUser(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['admin']);
    }

    /**
     * Vérifie si l'utilisateur courant a le rôle admin.
     *
     * @param array{id?: int, username?: string, role?: string} $user
     */
    public static function isAdmin(array $user): bool
    {
        return ($user['role'] ?? null) === 'admin';
    }

    /**
     * Force la vérification d'authentification et redirige vers /login si non connecté.
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }
}
