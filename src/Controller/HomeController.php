<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\EventService;
use CapsuleLib\Core\RenderController;

class HomeController extends RenderController
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
