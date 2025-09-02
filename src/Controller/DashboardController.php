<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use CapsuleLib\Security\Authenticator;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use App\Lang\TranslationLoader;
use CapsuleLib\Service\UserService;
use CapsuleLib\Service\PasswordService;
use App\Service\EventService;

final class DashboardController extends RenderController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly EventService $eventService,
        private readonly PasswordService $passwords,
    ) {}

    /** Cache par requête */
    private ?array $strings = null;
    private ?array $links   = null;

    /** ---- DRY helpers ---- */
    private function strings(): array
    {
        return $this->strings ??= TranslationLoader::load(defaultLang: 'fr');
    }

    // TODO: Idéalement, injecter un UrlGenerator et une ACL pour filtrer par rôle.
    private function links(bool $isAdmin): array
    {
        if ($this->links !== null) {
            return $this->links;
        }

        $links = [
            ['title' => 'Accueil',      'url' => 'home',     'icon' => 'home'],
            ['title' => 'Utilisateurs', 'url' => 'users',    'icon' => 'users'],
            ['title' => 'Mes articles', 'url' => 'articles', 'icon' => 'articles'],
            ['title' => 'Mon compte',   'url' => 'account',  'icon' => 'account'],
            ['title' => 'Déconnexion',  'url' => '/logout',  'icon' => 'logout'],
        ];

        if (!$isAdmin) {
            $links = array_values(array_filter($links, fn($l) => $l['url'] !== 'users'));
        }

        return $this->links = $links;
    }

    private function currentUser(): array
    {
        // TODO: itération B: injecter CurrentUserInterface plutôt que statique
        return Authenticator::getUser() ?? [];
    }

    private function isAdmin(array $user): bool
    {
        return ($user['role'] ?? null) === 'admin';
    }

    /** Base payload commun au layout Dashboard */
    private function basePayload(array $extra = []): array
    {
        $user    = $this->currentUser();
        $isAdmin = $this->isAdmin($user);

        $base = [
            'isDashboard'      => true,
            'title'            => '',
            'user'             => $user,
            'username'         => $user['username'] ?? '',
            'isAdmin'          => $isAdmin,
            'links'            => $this->links($isAdmin),
            'str'              => $this->strings(),
            'dashboardContent' => null,
        ];

        return array_replace($base, $extra);
    }

    /**
     * Point unique de rendu du dashboard.
     */
    private function renderDashboard(
        string $title,
        ?string $component = null,
        array $componentVars = [],
        bool $requireAdmin = false,
        bool $requireAuth = true,
    ): void {
        if ($requireAdmin) {
            AuthMiddleware::requireRole('admin');
        } elseif ($requireAuth) {
            AuthMiddleware::handle();
        }

        $content = null;
        if ($component !== null) {
            $componentVars += ['str' => $this->strings()];
            $content = $this->renderComponent($component, $componentVars);
        }

        echo $this->renderView('dashboard/home.php', $this->basePayload([
            'title'            => $title,
            'dashboardContent' => $content,
        ]));
    }

    /** ---- Actions minces ---- */

    public function home(): void
    {
        $this->renderDashboard('Dashboard');
    }

    public function account(): void
    {
        // Consommer les flash/errors de la session (PRG)
        $flash  = $_SESSION['flash']  ?? null;
        unset($_SESSION['flash']);
        $errors = $_SESSION['errors'] ?? null;
        unset($_SESSION['errors']);

        $this->renderDashboard('Mon compte', 'dash_account.php', [
            'user'                  => $this->currentUser(),
            'flash'                 => $flash,
            'errors'                => $errors,
            'accountPasswordAction' => '/dashboard/account/password',
        ]);
    }

    /** POST /dashboard/account/password */
    public function accountPassword(): void
    {
        AuthMiddleware::handle();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /dashboard/account', true, 303);
            return;
        }

        $userId = (int)($this->currentUser()['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $old     = trim($_POST['old_password'] ?? '');
        $new     = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_new_password'] ?? '');

        $errors = [];
        if ($new !== $confirm) {
            $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
        }

        if (!$errors) {
            [$ok, $svcErrors] = $this->passwords->changePassword($userId, $old, $new);
            if ($ok) {
                $_SESSION['flash'] = 'Mot de passe modifié avec succès.';
            } else {
                $errors = $svcErrors;
            }
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
        }

        // PRG
        header('Location: /dashboard/account', true, 303);
    }

    public function users(): void
    {
        AuthMiddleware::requireRole('admin');
        $users = $this->userService->getAllUsers();
        echo $this->renderView('dashboard/home.php', [
            'title' => 'Utilisateurs',
            'isDashboard' => true,
            'links' => $this->links(true),
            'isAdmin' => true,
            'dashboardContent' => $this->renderComponent('dash_users.php', [
                'users' => $users,
                'str' => $this->strings(),
                'createAction' => '/dashboard/users/create',
                'deleteAction' => '/dashboard/users/delete',
            ]),
            'str' => $this->strings(),
        ]);
    }

    public function usersCreate(): void
    {
        AuthMiddleware::requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /dashboard/users', true, 303);
            return;
        }
        $username = filter_input(INPUT_POST, 'username');
        $password = $_POST['password'] ?? null;
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $role     = $_POST['role'] ?? 'employee';

        if ($username && $password && $email) {
            $this->userService->createUser($username, $password, $email, $role);
            $_SESSION['flash'] = "Utilisateur créé avec succès.";
        } else {
            $_SESSION['flash'] = "Erreur : champs invalides.";
        }
        header('Location: /dashboard/users', true, 303);
    }

    public function usersDelete(): void
    {
        AuthMiddleware::requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /dashboard/users', true, 303);
            return;
        }
        $ids = array_map('intval', $_POST['user_ids'] ?? []);
        foreach ($ids as $id) {
            $this->userService->deleteUser($id);
        }
        $_SESSION['flash'] = "Utilisateur(s) supprimé(s).";
        header('Location: /dashboard/users', true, 303);
    }

    public function articles(): void
    {
        $articles = $this->eventService->getAll();
        foreach ($articles as &$article) {
            $author = $this->userService->getUserById($article->author_id);
            $article->author = $author->username ?? 'Inconnu';
        }
        $this->renderDashboard('Mes articles', 'dash_articles.php', [
            'articles' => $articles,
        ]);
    }

    public function index(): void
    {
        $this->home();
    }
}
