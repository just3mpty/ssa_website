<?php

declare(strict_types=1);

namespace App\Lang;

class Translate
{
    /** @var array<string, string> Chaînes traduites disponibles */
    private static array $lang = [];

    public static function detect_and_load(string $default = 'fr', string $page = 'common'): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $langCode = $_GET['lang'] ?? $_SESSION['lang'] ?? $default;
        $_SESSION['lang'] = $langCode;

        $basePath = __DIR__ . "/locales/{$langCode}";
        $commonFile = "{$basePath}/common.php";
        $pageFile   = "{$basePath}/{$page}.php";

        if (!file_exists($commonFile)) {
            $basePath   = __DIR__ . "/locales/{$default}";
            $commonFile = "{$basePath}/common.php";
            $pageFile   = "{$basePath}/{$page}.php";
        }

        $common = file_exists($commonFile) ? include $commonFile : [];
        $page   = file_exists($pageFile) ? include $pageFile : [];

        self::$lang = array_merge($common, $page);
    }

    public static function action(string $key): string
    {
        return self::$lang[$key] ?? $key;
    }
}
