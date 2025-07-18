<?php

declare(strict_types=1);

namespace CapsuleLib\Security;

use PDO;

class Authenticator
{
    public static function login(PDO $pdo, string $username, string $password): bool
    {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
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
        // S'assurer que la session est démarrée
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Vider toutes les données de session
        $_SESSION = [];

        // Supprimer le cookie de session si existant
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Détruire la session serveur
        session_destroy();
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
