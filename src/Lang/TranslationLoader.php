<?php

declare(strict_types=1);

namespace App\Lang;

use App\Lang\Translate;

/**
 * Classe utilitaire pour charger un ensemble standardisé de chaînes de traduction multilingue.
 *
 * Cette classe permet de centraliser le chargement de toutes les clés de traduction
 * utilisées dans l'application, en s'appuyant sur la logique de détection automatique de langue
 * fournie par `Translate::detect_and_load()`.
 *
 * Elle renvoie un tableau associatif (`clé => traduction`) couvrant :
 * - le layout (meta, nav, footer),
 * - les pages (hero, à propos, agenda, partenaires...),
 * - les composants (formulaires, filtres),
 * - les interfaces admin, etc.
 *
 * @package App\Lang
 */
class TranslationLoader
{
    /**
     * Charge dynamiquement toutes les chaînes de traduction nécessaires à une vue complète.
     *
     * Utilise un tableau figé de clés attendues dans l’interface et appelle `Translate::action()` pour chacune.
     * La langue courante est également injectée sous la clé `'lang'`.
     *
     * @param string $defaultLang Langue par défaut à utiliser en fallback (ex: `'fr'`)
     * @param string $page Nom du fichier de traduction spécifique à charger (ex: `'home'`, `'agenda'`, `'admin'`)
     *
     * @return array<string, string> Tableau associatif contenant toutes les chaînes traduites.
     */
    public static function load(string $defaultLang = 'fr'): array
    {
        Translate::detect_and_load($defaultLang);
        $out = Translate::all();
        $out['lang'] = $_SESSION['lang'] ?? $defaultLang;
        return $out;
    }
}
