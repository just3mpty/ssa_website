<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use App\Lang\TranslationLoader;
use CapsuleLib\Service\UserService;
use CapsuleLib\Service\PasswordService;
use App\Service\ArticleService;
use App\Navigation\SidebarLinksProvider;
use CapsuleLib\Security\CurrentUserProvider;
use CapsuleLib\Http\Redirect; // <-- helper de redirection (si tu l'as ajouté)

final class DashboardController extends RenderController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ArticleService $articleService,
        private readonly PasswordService $passwords,
        private readonly SidebarLinksProvider $linksProvider,
    ) {}

    /** Cache par requête */
    private ?array $strings = null;

    /** ---- DRY helpers ---- */
    private function strings(): array
    {
        return $this->strings ??= TranslationLoader::load(defaultLang: 'fr');
    }

    private function links(bool $isAdmin): array
    {
        return $this->linksProvider->get($isAdmin);
    }

    /** Base payload commun au layout Dashboard */
    private function basePayload(array $extra = []): array
    {
        $user    = CurrentUserProvider::getUser() ?? [];
        $isAdmin = ($user['role'] ?? null) === 'admin';

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
    ): void {
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
        // PRG (flash/errors)
        $flash  = $_SESSION['flash']  ?? null;
        unset($_SESSION['flash']);
        $errors = $_SESSION['errors'] ?? null;
        unset($_SESSION['errors']);

        $this->renderDashboard('Mon compte', 'dash_account.php', [
            'flash'                 => $flash,
            'errors'                => $errors,
            'accountPasswordAction' => '/dashboard/account/password',
        ]);
    }

    /** POST /dashboard/account/password */
    public function accountPassword(): void
    {
        $user = CurrentUserProvider::getUser();
        $userId = (int)($user['id'] ?? 0);
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
        Redirect::to('/dashboard/account'); // ou header('Location: /dashboard/account', true, 303);
    }

    public function users(): void
    {
        // L’accès admin est géré par le middleware au niveau du routeur
        $users = $this->userService->getAllUsers();

        $flash  = $_SESSION['flash']  ?? null;
        unset($_SESSION['flash']);
        $errors = $_SESSION['errors'] ?? null;
        unset($_SESSION['errors']);

        echo $this->renderView('dashboard/home.php', [
            'title'            => 'Utilisateurs',
            'isDashboard'      => true,
            'links'            => $this->links(true),
            'isAdmin'          => true,
            'dashboardContent' => $this->renderComponent('dash_users.php', [
                'users'        => $users,
                'flash'        => $flash,
                'errors'       => $errors,
                'str'          => $this->strings(),
                'createAction' => '/dashboard/users/create',
                'deleteAction' => '/dashboard/users/delete',
            ]),
            'str'              => $this->strings(),
        ]);
    }

    public function usersCreate(): void
    {
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

        Redirect::to('/dashboard/users');
    }

    public function usersDelete(): void
    {
        $ids = array_map('intval', $_POST['user_ids'] ?? []);
        foreach ($ids as $id) {
            $this->userService->deleteUser($id);
        }
        $_SESSION['flash'] = "Utilisateur(s) supprimé(s).";

        Redirect::to('/dashboard/users');
    }

    public function index(): void
    {
        $this->home();
    }
}
