<?php

declare(strict_types=1);

namespace App\Lang;

/**
 * Classe statique de gestion des traductions multilingues.
 *
 * Cette classe permet de charger dynamiquement les fichiers de traduction
 * depuis un dossier `lang/{code}/` et d'accéder aux chaînes traduites via des clés.
 * Elle prend en charge la persistance de la langue via `$_SESSION`, et un fallback.
 *
 * Exemple d’utilisation :
 * ```php
 * Translate::load(default: 'fr', page: 'index');       // charge fr/index.php + fr/common.php
 * echo Translate::t('nav_home');                       // affiche la chaîne traduite
 * ```
 *
 * @version 1.0
 */
class Translate
{
    /** @var array<string, string> Chaînes traduites disponibles */
    private static array $lang = [];

    /**
     * Charge les fichiers de traduction depuis le dossier de la langue choisie.
     *
     * La langue est déterminée par l’ordre de priorité suivant :
     *   1. `$_GET['lang']`
     *   2. `$_SESSION['lang']`
     *   3. `$default` (valeur par défaut, ex. 'fr')
     *
     * Deux fichiers sont chargés :
     *   - `common.php` : contenu partagé
     *   - `{$page}.php` : contenu spécifique à une page donnée
     *
     * Si le dossier ou les fichiers sont manquants, un fallback vers la langue par défaut est utilisé.
     *
     * @param string $default Langue par défaut (ex. 'fr')
     * @param string $page    Nom du fichier spécifique à charger (sans extension)
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
     * Récupère une chaîne traduite à partir de sa clé.
     *
     * Si la clé est introuvable dans les fichiers chargés, elle est retournée telle quelle.
     *
     * @param string $key Clé de la chaîne traduite
     * @return string Texte traduit ou la clé en fallback
     */
    public static function action(string $key): string
    {
        return self::$lang[$key] ?? $key;
    }
}
