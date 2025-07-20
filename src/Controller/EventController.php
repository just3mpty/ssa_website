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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Authenticator::getUser();
            $eventModel->create([
                'titre' => $_POST['titre'],
                'description' => $_POST['description'],
                'date_event' => $_POST['date_event'],
                'lieu' => $_POST['lieu'],
                'image' => null,
                'author_id' => $user['id'],
            ]);
            header('Location: /events');
            exit;
        }

        echo $this->renderView('pages/create_event.php');
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
