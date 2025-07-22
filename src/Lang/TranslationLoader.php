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
    public static function load(string $defaultLang = 'fr', string $page = 'common'): array
    {
        Translate::detect_and_load(default: $defaultLang, page: $page);

        $keys = [

            // --- Système ---
            'lang',

            // --- META ---
            'meta_description',
            'meta_keywords',
            'meta_author',
            'page_title',

            // --- Navigation ---
            'nav_title',
            'nav_home',
            'nav_apropos',
            'nav_actualites',
            'nav_agenda',
            'nav_project',
            'nav_galerie',
            'nav_contact',
            'lang_fr',
            'lang_br',

            // --- Footer ---
            'footer_address',
            'footer_tel',
            'footer_email_1',
            'footer_email_2',
            'footer_siret',
            'footer_copyright',

            // --- Hero ---
            'hero_title',
            'hero_slogan',
            'hero_cta_more',
            'hero_cta_participate',
            'hero_cta_contact',

            // --- À propos ---
            'about_title',
            'about_subtitle',
            'about_intro',
            'about_axes',
            'about_axes_1',
            'about_axes_2',
            'about_goal_label',
            'about_goal',
            'about_cta',
            'about_logo_alt',

            // --- Agenda ---
            'agenda_intro',
            'no_upcoming_events',

            // --- Partenaires ---
            'partners_title',

            // --- Contact ---
            'contact_title',
            'contact_intro',
            'contact_coords_title',
            'contact_address_label',
            'contact_address',
            'contact_phone_label',
            'contact_phone',
            'contact_email_label',
            'contact_email1',
            'contact_email2',
            'contact_form_title',
            'contact_form_name',
            'contact_form_email',
            'contact_form_message',
            'contact_form_submit',

            // --- Actualités ---
            'news_title',
            'news_filter_all',
            'news_filter_sante',
            'news_filter_env',
            'news_filter_mob',
            'read_more',

            // --- Authentification ---
            'login_title',
            'login_username',
            'login_password',
            'login_submit',

            // --- Admin/Dashboard ---
            'welcome',
            'dashboard_title',
            'admin_manage_events',
            'admin_contacts',
            'admin_create_event',
            'logout',

            // --- Événements : création ---
            'create_event_title',
            'create_event_label_title',
            'create_event_label_desc',
            'create_event_label_date',
            'create_event_label_time',
            'create_event_label_place',
            'create_event_submit',
        ];

        $out = [];

        foreach ($keys as $key) {
            $out[$key] = Translate::action($key);
        }
        // surcharge manuelle de la langue actuelle
        $out['lang'] = $_SESSION['lang'] ?? $defaultLang;

        return $out;
    }
}
