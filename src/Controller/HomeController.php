<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
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
        return TranslationLoader::load(defaultLang: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php'));
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
