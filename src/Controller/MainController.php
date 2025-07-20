<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Event;
use CapsuleLib\Service\Database\SqliteConnection;
use CapsuleLib\Framework\ViewController;

/**
 * Contrôleur principal du site.
 *
 * Gère les pages publiques statiques telles que l'accueil, les actualités, la galerie, etc.
 * Chaque méthode correspond à une route définie dans `config/routes.php`.
 */
class MainController extends  ViewController
{

    /**
     * Page d'accueil du site.
     *
     * @return void
     */
    public function home(): void
    {
        $pdo = SqliteConnection::getInstance();
        $eventModel = new Event($pdo);
        $events = $eventModel->upcoming();

        echo $this->renderView('pages/home.php', [
            'events' => $events, // Ajoute explicitement
        ]);
    }

    /**
     * Page de présentation du projet.
     *
     * @return void
     */
    public function projet(): void
    {
        echo $this->renderView('pages/projet.php', ['title' => 'Projet']);
    }

    /**
     * Page de participation / appel à contribution.
     *
     * @return void
     */
    public function participer(): void
    {
        echo $this->renderView('pages/participer.php', ['title' => 'Participer']);
    }

    /**
     * Galerie multimédia (images, vidéos…).
     *
     * @return void
     */
    public function galerie(): void
    {
        echo $this->renderView('pages/galerie.php', ['title' => 'Galerie']);
    }

    /**
     * Page de wiki / documentation / base de connaissances.
     *
     * @return void
     */
    public function wiki(): void
    {
        echo $this->renderView('pages/wiki.php', ['title' => 'Wiki']);
    }
}
