<?php

namespace App\Controller;

use App\Model\Event;
use CapsuleLib\Framework\AbstractController;
use CapsuleLib\Service\Database\SqliteConnection;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use CapsuleLib\Security\Authenticator;

class EventController extends AbstractController
{
    // /events : Public
    public function listEvents(): void
    {
        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);
        $events = $eventModel->upcoming();

        // Vérifie rôle pour le bouton
        $isAdmin = false;
        if (Authenticator::isAuthenticated()) {
            $user = Authenticator::getUser();
            $isAdmin = ($user['role'] ?? '') === 'admin';
        }

        echo $this->renderView('admin/dashboard.php', [
            'events' => $events,
            'isAdmin' => $isAdmin,
        ]);
    }

    // /events/create : Admin only
    public function createEvent(): void
    {
        AuthMiddleware::requireRole('admin');

        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);

        $errors = [];
        $data = [
            'titre' => '',
            'description' => '',
            'date_event' => '',
            'lieu' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF CHECK (à ajouter si tu implémentes un CsrfTokenManager)
            // if (!CsrfTokenManager::verify($_POST['csrf_token'])) { ... }

            // Sanitize/Validate
            $data = [
                'titre' => trim($_POST['titre'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'date_event' => $_POST['date_event'] ?? '',
                'lieu' => trim($_POST['lieu'] ?? ''),
            ];
            foreach ($data as $key => $val) {
                if ($val === '') $errors[$key] = 'Ce champ est obligatoire.';
            }
            // Validate format date
            if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $data['date_event'])) {
                $errors['date_event'] = "Format date/heure invalide";
            }

            if (empty($errors)) {
                $user = Authenticator::getUser();
                $eventModel->create([
                    ...$data,
                    'date_event' => str_replace('T', ' ', $data['date_event']), // format SQL DATETIME
                    'image' => null,
                    'author_id' => $user['id'],
                ]);
                header('Location: /events');
                exit;
            }
        }

        echo $this->renderView('admin/create_event.php', [
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    // /events/edit/{id} : Admin only
    public function editEvent($id): void
    {
        AuthMiddleware::requireRole('admin');

        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);
        $event = $eventModel->find($id);

        if (!$event) {
            http_response_code(404);
            echo "Événement introuvable";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventModel->update($id, [
                'titre' => $_POST['titre'],
                'description' => $_POST['description'],
                'date_event' => $_POST['date_event'],
                'lieu' => $_POST['lieu'],
                'image' => null,
            ]);
            header('Location: /events');
            exit;
        }

        echo $this->renderView('pages/edit_event.php', [
            'event' => $event,
        ]);
    }

    // /events/delete/{id} : Admin only (POST recommandé)
    public function deleteEvent($id): void
    {
        AuthMiddleware::requireRole('admin');

        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventModel->delete($id);
            header('Location: /events');
            exit;
        }

        // Option : affiche une page de confirmation
        echo $this->renderView('pages/delete_event.php', [
            'eventId' => $id,
        ]);
    }
}
