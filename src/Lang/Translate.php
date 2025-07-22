<?php

declare(strict_types=1);

namespace App\Lang;

/**
 * Gestionnaire statique de traductions multilingues.
 *
 * Cette classe charge les fichiers de langue dynamiquement selon :
 * - la langue demandée via `$_GET['lang']`
 * - la langue en session `$_SESSION['lang']`
 * - ou une langue par défaut
 *
 * Elle fusionne les traductions communes et spécifiques à une page,
 * puis permet d’accéder aux chaînes traduites via une clé.
 *
 * Usage typique :
 * ```php
 * Translate::detect_and_load('fr', 'home');
 * echo Translate::action('nav_home');
 * ```
 *
 * @package App\Lang
 */
class Translate
{
    /**
     * Tableau associatif des traductions chargées.
     *
     * @var array<string, string>
     */
    private static array $lang = [];

    /**
     * Charge les traductions depuis les fichiers selon la langue détectée.
     *
     * Priorité de la langue :
     * 1. `$_GET['lang']`
     * 2. `$_SESSION['lang']`
     * 3. Langue par défaut passée en paramètre
     *
     * Charge deux fichiers de traduction :
     * - `common.php` : traductions globales communes
     * - `{page}.php` : traductions spécifiques à la page
     *
     * Si la langue demandée n’est pas disponible, recharge la langue par défaut.
     *
     * @param string $default Langue par défaut (ex. 'fr')
     * @param string $page Nom du fichier spécifique à charger (sans extension)
     *
     * @return void
     */
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

    /**
     * Récupère la chaîne traduite associée à une clé.
     *
     * Si la clé n’existe pas dans les traductions chargées, retourne la clé brute.
     *
     * @param string $key Clé de la chaîne traduite
     *
     * @return string Texte traduit ou clé brute en fallback
     */
    public static function action(string $key): string
    {
        return self::$lang[$key] ?? $key;
    }
}
