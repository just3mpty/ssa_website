<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use App\Navigation\SidebarLinksProvider;
use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Contracts\ViewRendererInterface;
use Capsule\Domain\Service\PasswordService;
use Capsule\Domain\Service\UserService;
use Capsule\Http\Message\Response;
use Capsule\Routing\Attribute\Route;
use Capsule\Routing\Attribute\RoutePrefix;
use Capsule\Security\CsrfTokenManager;
use Capsule\Security\CurrentUserProvider;
use Capsule\View\BaseController;

#[RoutePrefix('/dashboard')]
final class DashboardController extends BaseController
{
    private ?array $strings = null;

    public function __construct(
        private readonly UserService $users,
        private readonly PasswordService $passwords,
        private readonly SidebarLinksProvider $links,
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
    private function sidebarLinks(bool $isAdmin): array
    {
        return $this->links->get($isAdmin);
    }

    /**
     * Payload commun au layout Dashboard (shell)
     * @param array<string,mixed> $extra
     * @return array<string,mixed>
     */
    private function base(array $extra = []): array
    {
        $user = $this->currentUser();
        $isAdmin = ($user['role'] ?? null) === 'admin';

        $base = [
            // flags layout
            'showHeader' => false,
            'showFooter' => false,
            'isDashboard' => true,

            // i18n / user / nav
            'str' => $this->strings(),
            'user' => $user,
            'isAdmin' => $isAdmin,
            'links' => $this->sidebarLinks($isAdmin),

            // flash (PRG)
            'flash' => \Capsule\Http\Support\FlashBag::consume(),
        ];

        return array_replace($base, $extra);
    }

    /** Champ CSRF (HTML de confiance) */
    private function csrfInput(): string
    {
        return CsrfTokenManager::insertInput();
    }

    /**
     * Rend le shell dashboard + injecte un composant rendu dans {{{
     * dashboardContent }}}.
     *
     * @param string $title
     * @param string|null $component  ex: 'dash_account' (fichier: templates/components/dash_account.tpl.php)
     * @param array<string,mixed> $vars Variables passées au composant
     */
    private function renderDash(string $title, ?string $component = null, array $vars = []): Response
    {
        $content = null;

        if ($component !== null) {
            // IMPORTANT: on rend explicitement un composant
            // Chemin final: templates/components/<component>.tpl.php
            $content = $this->view->render(
                'components/' . $component . '.tpl.php',
                $vars + ['str' => $this->strings()]
            );
        }

        return $this->html('dashboard/home.tpl.php', $this->base([
            'title' => $title,
            'dashboardContent' => $content, // injecté dans le shell
        ]));
    }

    /* -------------------------------------------------------
     * Routes
     * ----------------------------------------------------- */

    /** GET /dashboard */
    #[Route(path: '', methods: ['GET'])]
    public function index(): Response
    {
        // Pas de composant spécifique → la zone de droite peut rester vide
        return $this->renderDash('Dashboard');
    }

    /** GET /dashboard/users (protégé par middleware admin) */
    #[Route(path: '/users', methods: ['GET'])]
    public function users(): Response
    {
        $errors = \Capsule\Http\Support\FormState::consumeErrors();
        $prefill = \Capsule\Http\Support\FormState::consumeData();

        return $this->renderDash('Utilisateurs', 'dash_users', [
            'users' => $this->users->getAllUsers(),
            'errors' => $errors,
            'prefill' => $prefill,
            'createAction' => '/dashboard/users/create',
            'deleteAction' => '/dashboard/users/delete',
            'csrf_input' => $this->csrfInput(),
        ]);
    }

    /** GET /dashboard/account */
    #[Route(path: '/account', methods: ['GET'])]
    public function account(): Response
    {
        $errors = \Capsule\Http\Support\FormState::consumeErrors();
        $prefill = \Capsule\Http\Support\FormState::consumeData();

        return $this->renderDash('Mon compte', 'dash_account', [
            'errors' => $errors,
            'prefill' => $prefill,
            'action' => '/dashboard/account/password', // attendu par le template
            'editUserAction' => '/dashboard/account/update',   // si tu ajoutes un formulaire d’édition
            'csrf_input' => $this->csrfInput(),
        ]);
    }

    /** GET /dashboard/agenda */
    #[Route(path: '/agenda', methods: ['GET'])]
    public function agenda(): Response
    {
        return $this->renderDash('Mon agenda', 'dash_agenda', [
            // ajoute ici les données de l’agenda si besoin
        ]);
    }

    /** POST /dashboard/account/password */
    #[Route(path: '/account/password', methods: ['POST'])]
    public function accountPassword(): Response
    {
        CsrfTokenManager::requireValidToken();

        $userId = (int) (($this->currentUser()['id'] ?? 0));
        if ($userId <= 0) {
            return $this->res->text('Forbidden', 403);
        }

        $old = trim((string)($_POST['old_password'] ?? ''));
        $new = trim((string)($_POST['new_password'] ?? ''));
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
                return $this->res
                    ->redirect('/dashboard/account', 302)
                    ->withHeader('Cache-Control', 'no-store');
            }
            $errors = $svcErrors ?: ['_global' => 'Échec de la modification du mot de passe.'];
        }

        \Capsule\Http\Support\FormState::set($errors, []);
        \Capsule\Http\Support\FlashBag::add('error', 'Le formulaire contient des erreurs.');

        return $this->res->redirect('/dashboard/account', 303);
    }
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
//         $this->userService->updateUser(
//         $id, ['email' => (string)$email, 'username' => $username, 'role' => $role]);
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
