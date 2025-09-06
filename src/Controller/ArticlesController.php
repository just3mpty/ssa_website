<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use App\Navigation\SidebarLinksProvider;
use App\Service\ArticleService;
use CapsuleLib\Core\RenderController;
use CapsuleLib\Http\RequestUtils;
use CapsuleLib\Http\FlashBag;
use CapsuleLib\Security\CsrfTokenManager;

final class ArticlesController extends RenderController
{
    public function __construct(
        private readonly ArticleService $articles,
        private readonly SidebarLinksProvider $linksProvider
    ) {}

    /* -------------------- Helpers -------------------- */

    private function str(): array
    {
        return TranslationLoader::load(defaultLang: 'fr');
    }

    private function links(bool $isAdmin): array
    {
        return $this->linksProvider->get($isAdmin);
    }

    private function renderDash(string $title, string $component, array $vars = []): void
    {

        echo $this->renderView('dashboard/home.php', [
            'title'            => $title,
            'isDashboard'      => true,
            'isAdmin'          => true,
            'user'             => $_SESSION['admin'] ?? [],
            'links'            => $this->links(true),
            'dashboardContent' => $this->renderComponent($component, $vars + ['str' => $this->str()]),
            'str'              => $this->str(),
        ]);
    }

    /** Normalise un id provenant du routeur (array|scalar). */
    private function normId(string|int|array $param): int
    {
        return RequestUtils::intFromParam($param);
    }

    /* -------------------- Listing -------------------- */

    public function index(): void
    {

        $list = $this->articles->getAll();
        $this->renderDash('Articles', 'dash_articles.php', [
            'articles'      => $list,
            'createUrl'     => '/dashboard/articles/create',
            'editBaseUrl'   => '/dashboard/articles/edit',
            'deleteBaseUrl' => '/dashboard/articles/delete',
            'csrf'          => CsrfTokenManager::getToken(),
        ]);
    }

    /* -------------------- Create -------------------- */

    public function createForm(): void
    {

        // PRG: réaffiche les erreurs/data si présents
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

        if (!RequestUtils::isPost()) {
            header('Location: /dashboard/articles', true, 303);
            return;
        }

        CsrfTokenManager::requireValidToken();

        // Délégation au service puis PRG
        $result = $this->articles->create($_POST, $_SESSION['admin'] ?? []);
        if (!empty($result['errors'])) {
            $_SESSION['errors'] = $result['errors'];
            $_SESSION['data']   = $result['data'] ?? $_POST;
            FlashBag::add('error', 'Le formulaire contient des erreurs.');
            header('Location: /dashboard/articles/create', true, 303);
            return;
        }

        FlashBag::add('success', 'Article créé.');
        header('Location: /dashboard/articles', true, 303);
    }

    /* -------------------- Edit -------------------- */

    public function editForm(string|array $params): void
    {

        $id = $this->normId($params);
        $article = $this->articles->find($id);
        if (!$article) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        // PRG: réaffiche les erreurs/data si présents
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

        if (!RequestUtils::isPost()) {
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

        $result = $this->articles->update($id, $_POST);
        if (!empty($result['errors'])) {
            $_SESSION['errors'] = $result['errors'];
            $_SESSION['data']   = $result['data'] ?? $_POST;
            FlashBag::add('error', 'Le formulaire contient des erreurs.');
            header("Location: /dashboard/articles/edit/{$id}", true, 303);
            return;
        }

        FlashBag::add('success', 'Article mis à jour.');
        header('Location: /dashboard/articles', true, 303);
    }

    /* -------------------- Delete -------------------- */

    public function deleteSubmit(string|array $params): void
    {

        if (!RequestUtils::isPost()) {
            header('Location: /dashboard/articles', true, 303);
            return;
        }

        CsrfTokenManager::requireValidToken();

        $id = $this->normId($params);
        $this->articles->delete($id);

        FlashBag::add('success', 'Article supprimé.');
        header('Location: /dashboard/articles', true, 303);
    }
}
