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

use CapsuleLib\Http\RequestUtils;
use CapsuleLib\Http\FlashBag;
use CapsuleLib\Http\Redirect;
use CapsuleLib\Http\FormState;
use CapsuleLib\Security\CsrfTokenManager;

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

    /* -------------------- Helpers -------------------- */

    private function str(): array
    {
        return $this->strings ??= TranslationLoader::load(defaultLang: 'fr');
    }

    private function links(bool $isAdmin): array
    {
        return $this->linksProvider->get($isAdmin);
    }

    /** Payload commun au layout Dashboard */
    private function basePayload(array $extra = []): array
    {
        $user    = CurrentUserProvider::getUser() ?? [];
        $isAdmin = ($user['role'] ?? null) === 'admin';

        $base = [
            'isDashboard' => true,
            'title'       => '',
            'user'        => $user,
            'username'    => $user['username'] ?? '',
            'isAdmin'     => $isAdmin,
            'links'       => $this->links($isAdmin),
            'str'         => $this->str(),
            // commun
            'flash'       => FlashBag::consume(),
        ];

        return array_replace($base, $extra);
    }

    /**
     * Point unique de rendu du dashboard (DRY).
     */
    private function renderDash(string $title, ?string $component = null, array $vars = []): void
    {
        $content = null;
        if ($component !== null) {
            $vars += ['str' => $this->str()];
            $content = $this->renderComponent($component, $vars);
        }

        echo $this->renderView('dashboard/home.php', $this->basePayload([
            'title'            => $title,
            'dashboardContent' => $content,
        ]));
    }

    /* -------------------- Routes -------------------- */

    public function index(): void
    {
        $this->home();
    }

    public function home(): void
    {
        $this->renderDash('Dashboard');
    }

    /* ===== Compte (GET/POST) ===== */

    public function account(): void
    {
        $errors  = FormState::consumeErrors();
        $prefill = FormState::consumeData();

        $this->renderDash('Mon compte', 'dash_account.php', [
            'errors'                => $errors,
            'accountPasswordAction' => '/dashboard/account/password',
            // éventuel pré-remplissage d’autres champs du compte
            'prefill'               => $prefill,
        ]);
    }

    /** POST /dashboard/account/password */
    public function accountPassword(): void
    {
        RequestUtils::ensurePostOrRedirect('/dashboard/account');
        CsrfTokenManager::requireValidToken();

        $user    = CurrentUserProvider::getUser();
        $userId  = (int)($user['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $old     = trim((string)($_POST['old_password'] ?? ''));
        $new     = trim((string)($_POST['new_password'] ?? ''));
        $confirm = trim((string)($_POST['confirm_new_password'] ?? ''));

        $errors = [];
        if ($new === '' || $old === '') {
            $errors['_global'] = 'Champs requis manquants.';
        } elseif ($new !== $confirm) {
            $errors['confirm_new_password'] = 'Les nouveaux mots de passe ne correspondent pas.';
        }

        if ($errors === []) {
            [$ok, $svcErrors] = $this->passwords->changePassword($userId, $old, $new);
            if ($ok) {
                Redirect::withSuccess('/dashboard/account', 'Mot de passe modifié avec succès.');
            }
            $errors = $svcErrors ?: ['_global' => 'Échec de la modification du mot de passe.'];
        }
        Redirect::withErrors('/dashboard/account', 'Le formulaire contient des erreurs.', $errors, []);
    }

    /* ===== Utilisateurs (admin) ===== */

    public function users(): void
    {
        // Accès admin géré par middleware
        $users = $this->userService->getAllUsers();

        $errors  = FormState::consumeErrors();
        $prefill = FormState::consumeData();

        $this->renderDash('Utilisateurs', 'dash_users.php', [
            'users'        => $users,
            'errors'       => $errors,
            'prefill'      => $prefill,
            'createAction' => '/dashboard/users/create',
            'deleteAction' => '/dashboard/users/delete',
        ]);
    }

    /** POST /dashboard/users/create */
    public function usersCreate(): void
    {
        RequestUtils::ensurePostOrRedirect('/dashboard/users');
        CsrfTokenManager::requireValidToken();

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: null;
        $role     = trim((string)($_POST['role'] ?? 'employee'));

        $errors = [];
        if ($username === '') $errors['username'] = 'Requis.';
        if ($password === '') $errors['password'] = 'Requis.';
        if (!$email)          $errors['email']    = 'Email invalide.';

        if ($errors !== []) {
            Redirect::withErrors(
                '/dashboard/users',
                'Le formulaire contient des erreurs.',
                $errors,
                ['username' => $username, 'email' => (string)$email, 'role' => $role]
            );
        }

        try {
            $this->userService->createUser($username, $password, (string)$email, $role);
            Redirect::withSuccess('/dashboard/users', 'Utilisateur créé avec succès.');
        } catch (\Throwable $e) {
            Redirect::withErrors(
                '/dashboard/users',
                'Erreur lors de la création.',
                ['_global' => 'Création impossible.'],
                ['username' => $username, 'email' => (string)$email, 'role' => $role]
            );
        }
    }

    /** POST /dashboard/users/delete */
    public function usersDelete(): void
    {
        RequestUtils::ensurePostOrRedirect('/dashboard/users');
        CsrfTokenManager::requireValidToken();

        $ids = array_map('intval', (array)($_POST['user_ids'] ?? []));
        $ids = array_values(array_filter($ids, fn(int $id) => $id > 0));

        if ($ids === []) {
            Redirect::withErrors('/dashboard/users', 'Aucun utilisateur sélectionné.', ['_global' => 'Aucun utilisateur sélectionné.']);
        }

        $deleted = 0;
        foreach ($ids as $id) {
            try {
                $this->userService->deleteUser($id);
                $deleted++;
            } catch (\Throwable $e) {
                // on continue pour les autres
            }
        }

        if ($deleted > 0) {
            Redirect::withSuccess('/dashboard/users', "Utilisateur(s) supprimé(s) : {$deleted}.");
        }
        Redirect::withErrors('/dashboard/users', 'Aucune suppression effectuée.', ['_global' => 'Aucune suppression effectuée.']);
    }
}
