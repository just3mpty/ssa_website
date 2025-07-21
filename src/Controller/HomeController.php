<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Framework\ViewController;
use App\Service\EventService;

class HomeController extends ViewController
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function home(): void
    {
        $events = $this->eventService->getUpcoming();
        echo $this->renderView('pages/home.php', [
            'events' => $events,
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
