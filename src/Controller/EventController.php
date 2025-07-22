<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Http\Middleware\AuthMiddleware;
use CapsuleLib\Security\Authenticator;
use App\Lang\Translate;
use CapsuleLib\Security\CsrfTokenManager;
use App\Service\EventService;
use CapsuleLib\Core\RenderController;

class EventController extends RenderController
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function listEvents(): void
    {
        $events = $this->eventService->getUpcoming();
        echo $this->renderView('pages/home.php', [
            'events' => $events,
        ]);
    }

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

            'create_event_title'       => Translate::action('create_event_title'),
            'create_event_label_title' => Translate::action('create_event_label_title'),
            'create_event_label_desc'  => Translate::action('create_event_label_desc'),
            'create_event_label_date'  => Translate::action('create_event_label_date'),
            'create_event_label_time'  => Translate::action('create_event_label_time'),
            'create_event_label_place' => Translate::action('create_event_label_place'),
            'create_event_submit'      => Translate::action('create_event_submit'),
        ];
    }

    public function createForm(): void
    {
        AuthMiddleware::requireRole('admin');
        echo $this->renderView('admin/create_event.php', [
            'errors' => [],
            'data'   => ['titre' => '', 'description' => '', 'date_event' => '', 'lieu' => ''],
            'str' => $this->getStrings(),
        ]);
    }

    public function createSubmit(): void
    {
        CsrfTokenManager::requireValidToken();
        AuthMiddleware::requireRole('admin');
        $result = $this->eventService->create($_POST, Authenticator::getUser());
        if (!empty($result['errors'])) {
            echo $this->renderView('admin/create_event.php', [
                'errors' => $result['errors'],
                'data'   => $result['data'] ?? $_POST,
                'str' => $this->getStrings(),
            ]);
            return;
        }
        header('Location: /events');
        exit;
    }

    public function editForm($id): void
    {
        AuthMiddleware::requireRole('admin');
        $event = $this->eventService->find((int)$id);
        if (!$event) {
            http_response_code(404);
            echo "Événement introuvable";
            return;
        }
        echo $this->renderView('pages/edit_event.php', [
            'event' => $event,
            'errors' => [],
            'str' => $this->getStrings(),
        ]);
    }

    public function editSubmit($id): void
    {
        CsrfTokenManager::requireValidToken();
        AuthMiddleware::requireRole('admin');
        $event = $this->eventService->find((int)$id);
        if (!$event) {
            http_response_code(404);
            echo "Événement introuvable";
            return;
        }
        $result = $this->eventService->update($id, $_POST);
        if (!empty($result['errors'])) {
            echo $this->renderView('pages/edit_event.php', [
                'event'  => array_merge($event, $result['data']),
                'errors' => $result['errors'],
                'str' => $this->getStrings(),
            ]);
            return;
        }
        header('Location: /events');
        exit;
    }

    public function deleteSubmit($id): void
    {
        AuthMiddleware::requireRole('admin');
        $this->eventService->delete((int)$id);
        header('Location: /events');
        exit;
    }
}
