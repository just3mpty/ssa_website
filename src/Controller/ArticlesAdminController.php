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

    /** TODO: factoriser dans un LinkProvider commun au dashboard */
    private function links(): array
    {
        return [
            ['title' => 'Accueil',      'url' => '/dashboard/home',     'icon' => 'home'],
            ['title' => 'Utilisateurs', 'url' => '/dashboard/users',    'icon' => 'users'],
            ['title' => 'Mes articles', 'url' => '/dashboard/articles', 'icon' => 'articles'],
            ['title' => 'Mon compte',   'url' => '/dashboard/account',  'icon' => 'account'],
            ['title' => 'Déconnexion',  'url' => '/logout',             'icon' => 'logout'],
        ];
    }

    private function renderDash(string $title, string $component, array $vars = []): void
    {
        AuthMiddleware::requireRole('admin');

        echo $this->renderView('dashboard/home.php', [
            'title'            => $title,
            'isDashboard'      => true,
            'isAdmin'          => true,
            'links'            => $this->links(),
            'dashboardContent' => $this->renderComponent($component, $vars + ['str' => $this->str()]),
            'str'              => $this->str(),
        ]);
    }

    /** Normalise un id provenant du routeur (array|scalar) */
    private function normId(string|int|array $param): int
    {
        if (\is_array($param)) {
            return (int)($param['id'] ?? 0);
        }
        return (int)$param;
    }

    /* ---------- Listing ---------- */

    public function index(): void
    {
        AuthMiddleware::requireRole('admin');

        $list = $this->articles->getAll();
        $this->renderDash('Articles', 'dash_articles.php', [
            'articles'      => $list,
            'createUrl'     => '/dashboard/articles/create',
            'editBaseUrl'   => '/dashboard/articles/edit',
            'deleteBaseUrl' => '/dashboard/articles/delete',
            'csrf'          => \CapsuleLib\Security\CsrfTokenManager::getToken(),
        ]);
    }

    /* ---------- Create ---------- */

    public function createForm(): void
    {
        AuthMiddleware::requireRole('admin');

        // Réaffiche les erreurs/data PRG si présents
        $errors = $_SESSION['errors'] ?? null;
        unset($_SESSION['errors']);
        $data   = $_SESSION['data']   ?? null;
        unset($_SESSION['data']);

        $this->renderDash('Créer un article', 'dash_article_form.php', [
            'action'  => '/dashboard/articles/create',
            'article' => $data ?? null,
            'errors'  => $errors,
            'csrf'    => CsrfTokenManager::getToken(),
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

        // même logique : on délègue au service puis PRG
        $result = $this->articles->create($_POST, $_SESSION['admin'] ?? []);
        if (!empty($result['errors'])) {
            $_SESSION['errors'] = $result['errors'];
            $_SESSION['data']   = $result['data'] ?? $_POST;
            header('Location: /dashboard/articles/create', true, 303);
            return;
        }

        $_SESSION['flash'] = 'Article créé.';
        header('Location: /dashboard/articles', true, 303);
    }

    /* ---------- Edit ---------- */

    public function editForm(string|array $params): void
    {
        AuthMiddleware::requireRole('admin');

        $id = $this->normId($params);
        $article = $this->articles->find($id);
        if (!$article) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        // Réaffiche les erreurs/data PRG si présents
        $prefill = $_SESSION['data'] ?? [];
        unset($_SESSION['data']);
        $errors  = $_SESSION['errors'] ?? null;
        unset($_SESSION['errors']);

        $this->renderDash('Modifier un article', 'dash_article_form.php', [
            'action'  => "/dashboard/articles/edit/{$id}",
            'article' => $prefill ? \array_replace($article, $prefill) : $article,
            'errors'  => $errors,
            'csrf'    => CsrfTokenManager::getToken(),
        ]);
    }

    public function editSubmit(string|array $params): void
    {
        AuthMiddleware::requireRole('admin');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /dashboard/articles', true, 303);
            return;
        }
        CsrfTokenManager::requireValidToken();

        $id = $this->normId($params);
        $article = $this->articles->find($id);
        if (!$article) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        // même logique : on délègue au service, ré-affiche si erreurs, sinon PRG
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

    /* ---------- Delete ---------- */

    public function deleteSubmit(string|array $params): void
    {
        AuthMiddleware::requireRole('admin');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /dashboard/articles', true, 303);
            return;
        }
        CsrfTokenManager::requireValidToken();

        $id = $this->normId($params);
        $this->articles->delete($id);

        $_SESSION['flash'] = 'Article supprimé.';
        header('Location: /dashboard/articles', true, 303);
    }
}
