<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use App\Navigation\SidebarLinksProvider;
use App\Lang\TranslationLoader;
use CapsuleLib\Service\PasswordService;
use CapsuleLib\Security\CurrentUserProvider;
use CapsuleLib\Http\RequestUtils;
use CapsuleLib\Http\FlashBag;
use CapsuleLib\Http\Redirect;
use CapsuleLib\Http\FormState;
use CapsuleLib\Security\CsrfTokenManager;
use CapsuleLib\Service\UserService;

final class DashboardController extends RenderController
{
    public function __construct(
        private readonly UserService $userService,
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
        $this->renderDash('Dashboard');
    }


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

    /* ===== Compte (GET/POST) ===== */
    public function account(): void
    {
        $errors  = FormState::consumeErrors();
        $prefill = FormState::consumeData();
        $user = CurrentUserProvider::getUser() ?? [];


        // header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        // header('Pragma: no-cache');
        // header('Expires: 0');

        $this->renderDash('Mon compte', 'dash_account.php', [
            'errors'                => $errors,
            'accountPasswordAction' => '/dashboard/account/password',
            'prefill'               => $prefill,
            'user'                  => $user,
            // ajout pour test UI modif user
            'editUserAction'        => '/dashboard/account/update', // à implémenter plus tard
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


    // public function usersUpdate(): void
    // {
    //     RequestUtils::ensurePostOrRedirect('/dashboard/users');
    //     //CsrfTokenManager::requireValidToken();
    //     $id       = (int)($_POST['id'] ?? 0);
    //     $username = trim((string)($_POST['username'] ?? ''));
    //     $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: null;
    //     $role     = trim((string)($_POST['role'] ?? 'employee'));
        

    //     $errors = [];
    //     if ($id <= 0)        $errors['_global'] = 'ID utilisateur invalide.';
    //     if ($username === '') $errors['username'] = 'Requis.';
    //     if (!$email)          $errors['email']    = 'Email invalide.';

    //     if ($errors !== []) {
    //         Redirect::withErrors(
    //             "/dashboard/users/{$id}",                
    //             'Le formulaire contient des erreurs.',
    //             $errors,
    //             ['username' => $username, 'email' => (string)$email, 'role' => $role]
    //         );
    //     }

    //     try {
    //         $input = ['username' => $username, 'email' => (string)$email, 'role' => $role];

    //         $this->userService->updateUser($id, $input);
    //         Redirect::withSuccess("/dashboard/account", 'Utilisateur modifié avec succès.');
    //     } catch (\Throwable $e) {
    //         Redirect::withErrors(
    //             // '/dashboard/users',
    //             '/dashboard/account',
    //             'Erreur lors de la modification.',
    //             ['_global' => 'Modification impossible.'],
    //             ['username' => $username, 'email' => (string)$email, 'role' => $role]
    //         );
    //     }
    // }


    // public function usersUpdate(): void
    // {
    //     RequestUtils::ensurePostOrRedirect('/dashboard/users');
    //     //CsrfTokenManager::requireValidToken();

    //     $id       = (int)($_POST['id'] ?? 0);
    //     $username = trim((string)($_POST['username'] ?? ''));
    //     $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: null;
    //     $role     = trim((string)($_POST['role'] ?? 'employee'));

    //     $errors = [];
    //     if ($id <= 0)        $errors['_global'] = 'ID utilisateur invalide.';
    //     if ($username === '') $errors['username'] = 'Requis.';
    //     if (!$email)          $errors['email']    = 'Email invalide.';

    //     if ($errors !== []) {
    //         Redirect::withErrors(
    //             '/dashboard/users',
    //             'Le formulaire contient des erreurs.',
    //             $errors,
    //             ['username' => $username, 'email' => (string)$email, 'role' => $role]
    //         );
    //     }

    //     try {
    //         $this->userService->updateUser($id, ['email' => (string)$email, 'username' => $username, 'role' => $role]);
    //         Redirect::withSuccess('/dashboard/users', 'Utilisateur modifié avec succès.');
    //     } catch (\Throwable $e) {
    //         Redirect::withErrors(
    //             '/dashboard/users',
    //             'Erreur lors de la modification.',
    //             ['_global' => 'Modification impossible.'],
    //             ['username' => $username, 'email' => (string)$email, 'role' => $role]
    //         );
    //     }
    // }


}
