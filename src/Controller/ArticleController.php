<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use App\Service\ArticleService;
use CapsuleLib\Core\RenderController;

/**
 * Contrôleur pour la gestion des événements.
 *
 * Gère la liste, la création, la modification et la suppression des événements.
 * Applique les restrictions d'accès pour les opérations sensibles (création, édition, suppression).
 *
 * @package App\Controller
 */
class ArticleController extends RenderController
{
    /**
     * Service d'accès et manipulation des événements.
     */
    private ArticleService $articleService;

    /**
     * Constructeur.
     *
     * @param ArticleService $articleService Service pour manipuler les événements.
     */
    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * Affiche la liste des événements à venir.
     *
     * @return void
     */
    public function listArticles(): void
    {
        $articles = $this->articleService->getUpcoming();
        echo $this->renderView('pages/home.php', [
            'articles' => $articles,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouvel événement.
     * Restreint aux utilisateurs avec rôle 'admin'.
     *
     * @return void
     */
    // Optionnel : détail public
    // public function show(int $id): void
    // {
    //     $article = $this->articleService->findPublic($id); // filtre statut, dates...
    //     if (!$article) {
    //         http_response_code(404);
    //         echo "Article introuvable";
    //         return;
    //     }
    //     echo $this->renderView('pages/article_show.php', ['article' => $article]);
    // }
}
