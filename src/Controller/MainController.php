<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\AbstractController;

/**
 * Contrôleur principal du site.
 *
 * Gère les pages publiques statiques telles que l'accueil, les actualités, la galerie, etc.
 * Chaque méthode correspond à une route définie dans `config/routes.php`.
 */
class MainController extends AbstractController
{
    /**
     * Page d'accueil du site.
     *
     * @return void
     */
    public function home(): void
    {
        echo $this->renderView('pages/home.php', ['title' => 'Accueil']);
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
     * Page listant les actualités ou événements récents.
     *
     * @return void
     */
    public function actualites(): void
    {
        echo $this->renderView('pages/actualites.php', ['title' => 'Actualités']);
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
     * Page de présentation "à propos" ou historique du projet.
     *
     * @return void
     */
    public function apropos(): void
    {
        echo $this->renderView('pages/apropos.php', ['title' => 'À propos']);
    }

    /**
     * Page de contact, avec éventuelle gestion du formulaire.
     *
     * Si la requête est POST, traite les données et redirige.
     * Sinon, affiche simplement la page de contact.
     *
     * @return void
     */
    public function contact(): void
    {
        // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //     // Traitement formulaire ici (validation, envoi mail…)
        //     return $this->redirectToRoute('/contact', ['state' => 'success']);
        // }

        echo $this->renderView('pages/contact.php', ['title' => 'Contact']);
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
