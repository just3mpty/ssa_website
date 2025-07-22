<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Http\Middleware\AuthMiddleware;
use CapsuleLib\Security\Authenticator;
use App\Lang\TranslationLoader;
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
        return TranslationLoader::load(defaultLang: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php'));
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
