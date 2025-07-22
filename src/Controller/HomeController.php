<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\Translate;
use App\Service\EventService;
use CapsuleLib\Core\RenderController;

class HomeController extends RenderController
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Prépare les chaînes de traduction pour le layout et la page.
     */
    private function getStrings(): array
    {
        Translate::detect_and_load(default: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php'));

        return [
            'lang'              => $_SESSION['lang'] ?? 'fr',
            'meta_description' => Translate::action('meta_description'),
            'meta_keywords'    => Translate::action('meta_keywords'),
            'meta_author'      => Translate::action('meta_author'),
            'page_title'       => Translate::action('page_title'),
            'nav_title'        => Translate::action('nav_title'),
            'nav_home'         => Translate::action('nav_home'),
            'nav_apropos'      => Translate::action('nav_apropos'),
            'nav_actualites'   => Translate::action('nav_actualites'),
            'nav_agenda'       => Translate::action('nav_agenda'),
            'nav_project'      => Translate::action('nav_project'),
            'nav_galerie'      => Translate::action('nav_galerie'),
            'nav_contact'      => Translate::action('nav_contact'),
            'lang_fr'          => Translate::action('lang_fr'),
            'lang_br'          => Translate::action('lang_br'),
            'footer_address'   => Translate::action('footer_address'),
            'footer_tel'       => Translate::action('footer_tel'),
            'footer_email_1'   => Translate::action('footer_email_1'),
            'footer_email_2'   => Translate::action('footer_email_2'),
            'footer_siret'     => Translate::action('footer_siret'),
            'footer_copyright' => Translate::action('footer_copyright'),
            'hero_title'            => Translate::action('hero_title'),
            'hero_slogan'           => Translate::action('hero_slogan'),
            'hero_cta_more'         => Translate::action('hero_cta_more'),
            'hero_cta_participate'  => Translate::action('hero_cta_participate'),
            'hero_cta_contact'      => Translate::action('hero_cta_contact'),
            'agenda_intro' => Translate::action('agenda_intro'),
            'no_upcoming_events' => Translate::action('no_upcoming_events'),
            'partners_title' => Translate::action('partners_title'),
            'about_title'       => Translate::action('about_title'),
            'about_subtitle'    => Translate::action('about_subtitle'),
            'about_intro'       => Translate::action('about_intro'),
            'about_axes'        => Translate::action('about_axes'),
            'about_axes_1'      => Translate::action('about_axes_1'),
            'about_axes_2'      => Translate::action('about_axes_2'),
            'about_goal_label'  => Translate::action('about_goal_label'),
            'about_goal'        => Translate::action('about_goal'),
            'about_cta'         => Translate::action('about_cta'),
            'about_logo_alt'    => Translate::action('about_logo_alt'),
            'contact_title'           => Translate::action('contact_title'),
            'contact_intro'           => Translate::action('contact_intro'),
            'contact_coords_title'    => Translate::action('contact_coords_title'),
            'contact_address_label'   => Translate::action('contact_address_label'),
            'contact_address'         => Translate::action('contact_address'),
            'contact_phone_label'     => Translate::action('contact_phone_label'),
            'contact_phone'           => Translate::action('contact_phone'),
            'contact_email_label'     => Translate::action('contact_email_label'),
            'contact_email1'          => Translate::action('contact_email1'),
            'contact_email2'          => Translate::action('contact_email2'),
            'contact_form_title'      => Translate::action('contact_form_title'),
            'contact_form_name'       => Translate::action('contact_form_name'),
            'contact_form_email'      => Translate::action('contact_form_email'),
            'contact_form_message'    => Translate::action('contact_form_message'),
            'contact_form_submit'     => Translate::action('contact_form_submit'),
            'news_title'              => Translate::action('news_title'),
            'news_filter_all'         => Translate::action('news_filter_all'),
            'news_filter_sante'       => Translate::action('news_filter_sante'),
            'news_filter_env'         => Translate::action('news_filter_env'),
            'news_filter_mob'         => Translate::action('news_filter_mob'),
            'read_more'               => Translate::action('read_more'),
        ];
    }

    public function home(): void
    {
        echo $this->renderView('pages/home.php', [
            'str' => $this->getStrings(),
            'events'  => $this->eventService->getUpcoming(),
        ]);
    }

    public function projet(): void
    {
        echo $this->renderView('pages/projet.php', ['title' => 'Projet']);
    }

    public function galerie(): void
    {
        echo $this->renderView('pages/galerie.php', ['title' => 'Galerie']);
    }

    public function wiki(): void
    {
        echo $this->renderView('pages/wiki.php', ['title' => 'Wiki']);
    }
}
