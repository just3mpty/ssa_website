<?php

namespace App\Controller;

use App\Model\Event;
use CapsuleLib\Framework\ViewController;
use CapsuleLib\Service\Database\SqliteConnection;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use CapsuleLib\Security\Authenticator;

class EventController extends ViewController
{
    // /events : Public
    // GET /events
    public function listEvents(): void
    {
        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);
        $events = $eventModel->upcoming();

        $isAdmin = false;
        if (Authenticator::isAuthenticated()) {
            $user = Authenticator::getUser();
            $isAdmin = ($user['role'] ?? '') === 'admin';
        }

        echo $this->renderView('pages/events.php', [
            'events'  => $events,
            'isAdmin' => $isAdmin,
        ]);
    }

    // GET /events/create
    public function createForm(): void
    {
        AuthMiddleware::requireRole('admin');
        echo $this->renderView('admin/create_event.php', [
            'errors' => [],
            'data'   => ['titre' => '', 'description' => '', 'date_event' => '', 'lieu' => ''],
        ]);
    }

    // POST /events/create
    public function createSubmit(): void
    {
        AuthMiddleware::requireRole('admin');

        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);

        $errors = [];
        $data = [
            'titre' => trim($_POST['titre'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'date_event' => $_POST['date_event'] ?? '',
            'lieu' => trim($_POST['lieu'] ?? ''),
        ];

        foreach ($data as $key => $val) {
            if ($val === '') $errors[$key] = 'Ce champ est obligatoire.';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $data['date_event'])) {
            $errors['date_event'] = "Format date/heure invalide";
        }

        if (empty($errors)) {
            $user = Authenticator::getUser();
            $eventModel->create([
                ...$data,
                'date_event' => str_replace('T', ' ', $data['date_event']),
                'image'      => null,
                'author_id'  => $user['id'],
            ]);
            header('Location: /events');
            exit;
        }

        echo $this->renderView('admin/create_event.php', [
            'errors' => $errors,
            'data'   => $data,
        ]);
    }

    // GET /events/edit/{id}
    public function editForm($id): void
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

        echo $this->renderView('pages/edit_event.php', [
            'event' => $event,
            'errors' => [],
        ]);
    }
    // POST /events/edit/{id}
    public function editSubmit($id): void
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

        $data = [
            'titre' => trim($_POST['titre'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'date_event' => $_POST['date_event'] ?? '',
            'lieu' => trim($_POST['lieu'] ?? ''),
            'image' => null,
        ];

        $errors = [];
        foreach (['titre', 'description', 'date_event', 'lieu'] as $field) {
            if ($data[$field] === '') $errors[$field] = 'Ce champ est obligatoire.';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $data['date_event'])) {
            $errors['date_event'] = "Format date/heure invalide";
        }

        if ($errors) {
            // Redisplay the form with errors and previous data
            echo $this->renderView('pages/edit_event.php', [
                'event'  => array_merge($event, $data),
                'errors' => $errors,
            ]);
            return;
        }

        $eventModel->update($id, [
            ...$data,
            'date_event' => str_replace('T', ' ', $data['date_event']),
        ]);
        header('Location: /events');
        exit;
    }


    // POST /events/delete/{id}
    public function deleteSubmit($id): void
    {
        AuthMiddleware::requireRole('admin');

        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);

        $eventModel->delete($id);
        header('Location: /events');
        exit;
    }
}
