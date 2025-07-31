<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use App\Service\EventService;
use CapsuleLib\Core\RenderController;

/**
 * Contrôleur principal pour les pages publiques du site.
 *
 * Gère l’affichage des vues et la récupération des données nécessaires.
 * Intègre la gestion des traductions via TranslationLoader.
 *
 * @package App\Controller
 */
class HomeController extends RenderController
{
    /**
     * Service pour accéder aux événements.
     */
    private EventService $eventService;

    /**
     * Constructeur.
     *
     * @param EventService $eventService Service d'accès aux événements.
     */
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Prépare un tableau associatif de chaînes traduites pour layout et contenu.
     *
     * Charge les traductions dynamiquement selon la page actuelle.
     *
     * @return array<string, string> Tableau clé → chaîne traduite.
     */
    private function getStrings(): array
    {
        return TranslationLoader::load(defaultLang: 'fr');
    }

    /**
     * Affiche la page d’accueil avec les événements à venir et les traductions.
     *
     * @return void
     */
    public function home(): void
    {
        echo $this->renderView('pages/home.php', [
            'str'    => $this->getStrings(),
            'showHeader' => true,
            'showFooter' => true,
            'events' => $this->eventService->getUpcoming(),
        ]);
    }

    /**
     * Affiche la page "Projet".
     *
     * @return void
     */
    public function projet(): void
    {
        echo $this->renderView('pages/projet.php', [
            'str'    => $this->getStrings(),
            'showHeader' => true,
            'showFooter' => true,
            'events' => $this->eventService->getUpcoming(),
        ]);
    }

    /**
     * Affiche la page "Galerie".
     *
     * @return void
     */
    public function galerie(): void
    {
        echo $this->renderView('pages/galerie.php', [
            'showHeader' => true,
            'showFooter' => true,
            'str'    => $this->getStrings(),
            'events' => $this->eventService->getUpcoming(),
        ]);
    }
}
