<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use CapsuleLib\Security\CsrfTokenManager;
use App\Lang\TranslationLoader;
use App\Service\ArticleService;

final class ArticlesAdminController extends RenderController
{
    public function __construct(private ArticleService $articles) {}

    private function str(): array
    {
        return TranslationLoader::load(defaultLang: 'fr');
    }

    private function renderDash(string $title, string $component, array $vars = []): void
    {
        AuthMiddleware::requireRole('admin');
        echo $this->renderView('dashboard/home.php', [
            'title'            => $title,
            'isDashboard'      => true,
            'isAdmin'          => true,
            // injecte tes links/str si besoin selon ton layout
            'dashboardContent' => $this->renderComponent($component, $vars + ['str' => $this->str()]),
            'str'              => $this->str(),
        ]);
    }

    public function index(): void
    {
        AuthMiddleware::requireRole('admin');
        $list = $this->articles->getAll(); // pas seulement "upcoming" pour l’admin
        $this->renderDash('Articles', 'dash_articles.php', [
            'articles'      => $list,
            'createUrl'     => '/dashboard/articles/create',
            'editBaseUrl'   => '/dashboard/articles/edit/',
            'deleteBaseUrl' => '/dashboard/articles/delete/',
        ]);
    }

    public function createForm(): void
    {
        $this->renderDash('Créer un article', 'dash_article_form.php', [
            'action'  => '/dashboard/articles/create',
            'article' => null,
        ]);
    }

    public function createSubmit(): void
    {
        AuthMiddleware::requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /dashboard/articles', true, 303);
            return;
        }
        CsrfTokenManager::requireValidToken();

        $result = $this->articles->create($_POST, /* current user */ $_SESSION['admin'] ?? []);
        if (!empty($result['errors'])) {
            $_SESSION['errors'] = $result['errors'];
            $_SESSION['data']   = $result['data'] ?? $_POST;
            header('Location: /dashboard/articles/create', true, 303);
            return;
        }
        $_SESSION['flash'] = 'Article créé.';
        header('Location: /dashboard/articles', true, 303);
    }

    public function editForm(array $params): void
    {
        AuthMiddleware::requireRole('admin');
        $id = (int)($params['id'] ?? 0);
        $article = $this->articles->find($id);
        if (!$article) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $prefill = $_SESSION['data'] ?? [];
        unset($_SESSION['data']);
        $errors  = $_SESSION['errors'] ?? null;
        unset($_SESSION['errors']);

        $this->renderDash('Modifier un article', 'dash_article_form.php', [
            'action'  => "/dashboard/articles/edit/{$id}",
            'article' => array_replace($article, $prefill),
            'errors'  => $errors,
        ]);
    }

    public function editSubmit(array $params): void
    {
        AuthMiddleware::requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /dashboard/articles', true, 303);
            return;
        }
        CsrfTokenManager::requireValidToken();

        $id = (int)($params['id'] ?? 0);
        $article = $this->articles->find($id);
        if (!$article) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $result = $this->articles->update($id, $_POST);
        if (!empty($result['errors'])) {
            $_SESSION['errors'] = $result['errors'];
            $_SESSION['data']   = $result['data'] ?? $_POST;
            header("Location: /dashboard/articles/edit/{$id}", true, 303);
            return;
        }

        $_SESSION['flash'] = 'Article mis à jour.';
        header('Location: /dashboard/articles', true, 303);
    }

    public function deleteSubmit(array $params): void
    {
        AuthMiddleware::requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /dashboard/articles', true, 303);
            return;
        }
        CsrfTokenManager::requireValidToken();

        $id = (int)($params['id'] ?? 0);
        $this->articles->delete($id);

        $_SESSION['flash'] = 'Article supprimé.';
        header('Location: /dashboard/articles', true, 303);
    }
}
