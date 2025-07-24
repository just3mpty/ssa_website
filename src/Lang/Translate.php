<?php

declare(strict_types=1);

namespace App\Lang;

class Translate
{
    private static array $lang = [];

    public static function detect_and_load(string $default = 'fr'): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $langCode = $_GET['lang'] ?? $_SESSION['lang'] ?? $default;
        $_SESSION['lang'] = $langCode;

        $basePath = __DIR__ . "/locales/{$langCode}";
        $commonFile = "{$basePath}/index.php";

        if (!file_exists($commonFile)) {
            $basePath   = __DIR__ . "/locales/{$default}";
            $commonFile = "{$basePath}/index.php";
        }

        $common = file_exists($commonFile) ? include $commonFile : [];

        self::$lang = $common;
    }

    public static function action(string $key): string
    {
        return self::$lang[$key] ?? $key;
    }

    public static function all(): array
    {
        return self::$lang;
    }
}
