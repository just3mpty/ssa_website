<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Http\Middleware\AuthMiddleware;
use CapsuleLib\Security\Authenticator;
use App\Lang\TranslationLoader;
use CapsuleLib\Security\CsrfTokenManager;
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
     * Charge les chaînes de traduction pour la page courante.
     *
     * @return array<string, string> Tableau des traductions.
     */
    private function getStrings(): array
    {
        return TranslationLoader::load(defaultLang: 'fr');
    }

    /**
     * Affiche le formulaire de création d'un nouvel événement.
     * Restreint aux utilisateurs avec rôle 'admin'.
     *
     * @return void
     */
    public function createForm(): void
    {
        AuthMiddleware::requireRole('admin');
        echo $this->renderView('admin/create_article.php', [
            'errors' => [],
            'data'   => ['titre' => '', 'description' => '', 'date_article' => '', 'lieu' => ''],
            'str' => $this->getStrings(),
        ]);
    }

    /**
     * Traite la soumission du formulaire de création d'événement.
     * Valide le token CSRF, restreint à 'admin'.
     * En cas d'erreurs, réaffiche le formulaire avec messages d'erreur.
     * Sinon, redirige vers la liste des événements.
     *
     * @return void
     */
    public function createSubmit(): void
    {
        CsrfTokenManager::requireValidToken();
        AuthMiddleware::requireRole('admin');
        $result = $this->articleService->create($_POST, Authenticator::getUser());

        if (!empty($result['errors'])) {
            echo $this->renderView('admin/create_article.php', [
                'errors' => $result['errors'],
                'data'   => $result['data'] ?? $_POST,
                'str' => $this->getStrings(),
            ]);
            return;
        }

        header('Location: /articles');
        exit;
    }

    /**
     * Affiche le formulaire d'édition pour un événement donné.
     * Restreint aux 'admin'.
     * En cas d'événement introuvable, renvoie un 404.
     *
     * @param int|string $id Identifiant de l'événement.
     * @return void
     */
    public function editForm($id): void
    {
        AuthMiddleware::requireRole('admin');
        $article = $this->articleService->find((int)$id);

        if (!$article) {
            http_response_code(404);
            echo "Article introuvable";
            return;
        }

        echo $this->renderView('pages/edit_article.php', [
            'article' => $article,
            'errors' => [],
            'str' => $this->getStrings(),
        ]);
    }

    /**
     * Traite la soumission du formulaire d'édition d'un événement.
     * Valide CSRF, restreint à 'admin'.
     * En cas d'erreurs, réaffiche le formulaire avec erreurs.
     * Sinon, redirige vers la liste.
     * Si l'événement n'existe pas, renvoie 404.
     *
     * @param int|string $id Identifiant de l'événement à modifier.
     * @return void
     */
    public function editSubmit($id): void
    {
        CsrfTokenManager::requireValidToken();
        AuthMiddleware::requireRole('admin');
        $article = $this->articleService->find((int)$id);

        if (!$article) {
            http_response_code(404);
            echo "Article introuvable";
            return;
        }

        $result = $this->articleService->update($id, $_POST);

        if (!empty($result['errors'])) {
            echo $this->renderView('pages/edit_article.php', [
                'article'  => array_merge($article, $result['data']),
                'errors' => $result['errors'],
                'str' => $this->getStrings(),
            ]);
            return;
        }

        header('Location: /articles');
        exit;
    }

    /**
     * Supprime un événement par son identifiant.
     * Restreint aux 'admin'.
     * Redirige vers la liste des événements après suppression.
     *
     * @param int|string $id Identifiant de l'événement à supprimer.
     * @return void
     */
    public function deleteSubmit($id): void
    {
        AuthMiddleware::requireRole('admin');
        $this->articleService->delete((int)$id);
        header('Location: /articles');
        exit;
    }
}
