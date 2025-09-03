<?php

declare(strict_types=1);

namespace CapsuleLib\Security;

final class CurrentUserProvider
{
    /** @return array<string,mixed> */
    public static function getUser(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['admin']);
    }

    public static function isAdmin(array $user): bool
    {
        return ($user['role'] ?? null) === 'admin';
    }

    /**
     * Force la vérification d'authentification et redirige vers /login si non connecté.
     *
     * @return void
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }
}
