<?php

declare(strict_types=1);

namespace CapsuleLib\Security;

class CsrfTokenManager
{
    const TOKEN_KEY = '_csrf_token';

    public static function getToken(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    public static function insertInput(): string
    {
        $token = self::getToken();
        // Retourne un champ input hidden à insérer dans le formulaire
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function checkToken(?string $token): bool
    {
        return hash_equals(self::getToken(), (string)$token);
    }

    public static function requireValidToken(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf'] ?? '';
            if (!self::checkToken($token)) {
                http_response_code(403);
                die('CSRF token invalid. Action refused.');
            }
        }
    }
}
