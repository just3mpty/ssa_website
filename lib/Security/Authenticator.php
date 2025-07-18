<?php

declare(strict_types=1);

namespace CapsuleLib\Security;

use PDO;

class Authenticator
{
    public static function login(PDO $pdo, string $username, string $password): bool
    {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin'] = [
                'id'       => $user['id'],
                'username' => $user['username'],
            ];
            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin']);
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['admin']);
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }

    public static function getUser(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }
}
