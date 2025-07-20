<?php

namespace App\Controller;

use App\Model\Event;
use CapsuleLib\Framework\AbstractController;
use CapsuleLib\Service\Database\SqliteConnection;

class EventController extends AbstractController
{
    public function listEvents(): void
    {
        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);
        $events = $eventModel->upcoming();

        // Puis tu passes $events à ta vue :
        echo $this->renderView('pages/events.php', ['events' => $events]);
    }

    public function createEvent(): void
    {
        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventModel->create([
                'titre' => $_POST['titre'],
                'description' => $_POST['description'],
                'date_event' => $_POST['date_event'],
                'lieu' => $_POST['lieu'],
                'image' => null,
                'author_id' => $_SESSION['admin']['id'],
            ]);
            header('Location: /events');
            exit;
        }

        echo $this->renderView('pages/create_event.php');
    }
}
