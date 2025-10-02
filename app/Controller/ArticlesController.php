<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use App\Navigation\SidebarLinksProvider;
use App\Service\ArticleService;
use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Contracts\ViewRendererInterface;
use Capsule\Http\Message\Response;
use Capsule\Routing\Attribute\Route;
use Capsule\Routing\Attribute\RoutePrefix;
use Capsule\Security\CsrfTokenManager;
use Capsule\Security\CurrentUserProvider;
use Capsule\View\BaseController;

#[RoutePrefix('/dashboard/articles')]
final class ArticlesController extends BaseController
{
    private ?array $strings = null;

    public function __construct(
        private readonly ArticleService $articles,
        private readonly SidebarLinksProvider $linksProvider,
        ResponseFactoryInterface $res,
        ViewRendererInterface $view,
    ) {
        parent::__construct($res, $view);
    }

    /* -------------------------------------------------------
     * Helpers
     * ----------------------------------------------------- */

    /** @return array<string,string> */
    private function strings(): array
    {
        return $this->strings ??= TranslationLoader::load(defaultLang: 'fr');
    }

    /** @return array{id?:int,username?:string,role?:string,email?:string} */
    private function currentUser(): array
    {
        return CurrentUserProvider::getUser() ?? [];
    }

    /** @return list<array{title:string,url:string,icon:string}> */
    private function sidebarLinks(): array
    {
        $isAdmin = ($this->currentUser()['role'] ?? null) === 'admin';

        return $this->linksProvider->get($isAdmin);
    }

    private function csrfInput(): string
    {
        // renvoie le <input type="hidden" ...> (HTML “trusted”)
        return CsrfTokenManager::insertInput();
    }

    /**
     * Rendu du shell dashboard + composant central.
     * @param array<string,mixed> $vars
     */
    private function renderDash(string $title, string $componentPath, array $vars = []): Response
    {
        // 1) rendre le composant (ex: components/dash_articles.tpl.php)
        $componentHtml = $this->view->render('components/' . $componentPath, $vars + [
            'str' => $this->strings(),
        ]);

        // 2) rendre le shell dashboard
        return $this->html('dashboard/home.tpl.php', [
            'showHeader' => false,
            'showFooter' => false,
            'isDashboard' => true,
            'title' => $title,
            'links' => $this->sidebarLinks(),
            'user' => $this->currentUser(),
            'str' => $this->strings(),
            'flash' => \Capsule\Http\Support\FlashBag::consume(),
            'dashboardContent' => $componentHtml,
        ]);
    }

    /**
     * Mappe un ArticleDTO en VM pour la liste.
     * @param object $dto
     * @return array{id:int,titre:string,resume:string,date:string,author:string,editUrl:string,deleteUrl:string,showUrl:string}
     */
    private function mapListItem(object $dto): array
    {
        $id = (int)($dto->id ?? 0);
        $dateStr = '';
        if (!empty($dto->date_article)) {
            try {
                $dateStr = (new \DateTime((string)$dto->date_article))->format('d/m/Y');
            } catch (\Throwable) {
                $dateStr = (string)$dto->date_article;
            }
        }

        $editBase = '/dashboard/articles/edit';
        $deleteBase = '/dashboard/articles/delete';
        $showBase = '/dashboard/articles/show';

        return [
            'id' => $id,
            'titre' => (string)($dto->titre ?? ''),
            'resume' => (string)($dto->resume ?? ''),
            'date' => $dateStr,
            'author' => (string)($dto->author ?? 'Inconnu'),
            'editUrl' => rtrim($editBase, '/') . '/' . rawurlencode((string)$id),
            'deleteUrl' => rtrim($deleteBase, '/') . '/' . rawurlencode((string)$id),
            'showUrl' => rtrim($showBase, '/') . '/' . rawurlencode((string)$id),
        ];
    }

    /**
     * Mappe un ArticleDTO en VM pour le formulaire (créa/édition).
     * @param array<string,mixed>|object|null $src
     * @return array<string,mixed>
     */
    private function mapFormData(array|object|null $src): array
    {
        if ($src === null) {
            return [
                'titre' => '',
                'resume' => '',
                'description' => '',
                'date_article' => '',
                'hours' => '',
                'lieu' => '',
            ];
        }
        $a = is_object($src) ? get_object_vars($src) : $src;

        return [
            'titre' => (string)($a['titre'] ?? ''),
            'resume' => (string)($a['resume'] ?? ''),
            'description' => (string)($a['description'] ?? ''),
            'date_article' => (string)($a['date_article'] ?? ''),
            'hours' => (string)($a['hours'] ?? ''),
            'lieu' => (string)($a['lieu'] ?? ''),
        ];
    }

    /* -------------------------------------------------------
     * Routes
     * ----------------------------------------------------- */

    /** GET /dashboard/articles */
    #[Route(path: '', methods: ['GET'])]
    public function index(): Response
    {
        $list = $this->articles->getAll() ?? [];
        $items = array_map(fn ($dto) => $this->mapListItem($dto), $list);

        return $this->renderDash('Articles', 'dash_articles.tpl.php', [
            'createUrl' => '/dashboard/articles/create',
            'articles' => $items,
            'csrf_input' => $this->csrfInput(),
        ]);
    }

    /** GET /dashboard/articles/show/{id} */
    #[Route(path: '/show/{id}', methods: ['GET'])]
    public function show(int $id): Response
    {
        $dto = $this->articles->getById($id);
        if (!$dto) {
            return $this->res->text('Not Found', 404);
        }

        // VM simple pour la vue détail
        $vm = [
            'title' => (string)($dto->titre ?? ''),
            'summary' => (string)($dto->resume ?? ''),
            'description' => (string)($dto->description ?? ''),
            'date' => (string)($dto->date_article ?? ''),
            'time' => substr((string)($dto->hours ?? ''), 0, 5),
            'location' => (string)($dto->lieu ?? ''),
            'author' => (string)($dto->author ?? 'Inconnu'),
            'backUrl' => '/dashboard/articles',
        ];

        return $this->renderDash('Détail de l’article', 'dash_article_show.tpl.php', [
            'article' => $vm,
        ]);
    }

    /** GET /dashboard/articles/create */
    #[Route(path: '/create', methods: ['GET'])]
    public function createForm(): Response
    {
        $data = \Capsule\Http\Support\FormState::consumeData();
        $errors = \Capsule\Http\Support\FormState::consumeErrors();

        return $this->renderDash('Créer un article', 'dash_article_form.tpl.php', [
            'action' => '/dashboard/articles/create',
            'article' => $this->mapFormData($data),
            'errors' => $errors,
            'csrf_input' => $this->csrfInput(),
        ]);
    }

    /** POST /dashboard/articles/create */
    #[Route(path: '/create', methods: ['POST'])]
    public function createSubmit(): Response
    {
        CsrfTokenManager::requireValidToken();

        $current = $this->currentUser(); // si tu veux associer l’auteur
        $result = $this->articles->create($_POST, $current);
        if (!empty($result['errors'])) {
            \Capsule\Http\Support\FlashBag::add('error', 'Le formulaire contient des erreurs.');
            \Capsule\Http\Support\FormState::set($result['errors'], $result['data'] ?? $_POST);

            return $this->res->redirect('/dashboard/articles/create', 303);
        }

        \Capsule\Http\Support\FlashBag::add('success', 'Article créé.');

        return $this->res->redirect('/dashboard/articles', 302);
    }

    /** GET /dashboard/articles/edit/{id} */
    #[Route(path: '/edit/{id}', methods: ['GET'])]
    public function editForm(int $id): Response
    {
        $dto = $this->articles->getById($id);
        if (!$dto) {
            return $this->res->text('Not Found', 404);
        }

        $errors = \Capsule\Http\Support\FormState::consumeErrors();
        $prefill = \Capsule\Http\Support\FormState::consumeData();

        return $this->renderDash('Modifier un article', 'dash_article_form.tpl.php', [
            'action' => "/dashboard/articles/edit/{$id}",
            'article' => $this->mapFormData($prefill ?: get_object_vars($dto)),
            'errors' => $errors,
            'csrf_input' => $this->csrfInput(),
        ]);
    }

    /** POST /dashboard/articles/edit/{id} */
    #[Route(path: '/edit/{id}', methods: ['POST'])]
    public function editSubmit(int $id): Response
    {
        CsrfTokenManager::requireValidToken();

        if (!$this->articles->getById($id)) {
            return $this->res->text('Not Found', 404);
        }

        $result = $this->articles->update($id, $_POST);
        if (!empty($result['errors'])) {
            \Capsule\Http\Support\FlashBag::add('error', 'Le formulaire contient des erreurs.');
            \Capsule\Http\Support\FormState::set($result['errors'], $result['data'] ?? $_POST);

            return $this->res->redirect("/dashboard/articles/edit/{$id}", 303);
        }

        \Capsule\Http\Support\FlashBag::add('success', 'Article mis à jour.');

        return $this->res->redirect('/dashboard/articles', 302);
    }

    /** POST /dashboard/articles/delete/{id} */
    #[Route(path: '/delete/{id}', methods: ['POST'])]
    public function deleteSubmit(int $id): Response
    {
        CsrfTokenManager::requireValidToken();

        // idempotent : delete “silencieux”
        $this->articles->delete($id);

        \Capsule\Http\Support\FlashBag::add('success', 'Article supprimé.');

        return $this->res->redirect('/dashboard/articles', 303);
    }
}
