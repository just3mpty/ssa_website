<?php

namespace App\Lang;

use App\Lang\Translate;


class TranslationLoader
{
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

            // --- Dashboard ---
            'dashboard_title',
            'welcome',

            // --- Événements : création ---
            'create_event_title',
            'create_event_label_title',
            'create_event_label_desc',
            'create_event_label_date',
            'create_event_label_time',
            'create_event_label_place',
            'create_event_submit',

            // --- Admin
            'admin_manage_events',
            'admin_contacts',
            'admin_create_event',
            'logout'
        ];

        $out = [];

        foreach ($keys as $key) {
            $out[$key] = Translate::action($key);
        }

        $out['lang'] = $_SESSION['lang'] ?? $defaultLang;

        return $out;
    }
}
