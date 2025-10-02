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
use Capsule\Http\Support\FlashBag;
use Capsule\Http\Support\FormState;
use Capsule\Http\Support\Redirect;
use Capsule\Routing\Attribute\Route;
use Capsule\Routing\Attribute\RoutePrefix;
use Capsule\Security\CsrfTokenManager;
use Capsule\Security\CurrentUserProvider;
use Capsule\View\BaseController;

#[RoutePrefix('/dashboard')]
final class DashboardController extends BaseController
{
    public function __construct(
        private readonly UserService $users,
        private readonly PasswordService $passwords,
        private readonly SidebarLinksProvider $links,
        ResponseFactoryInterface $res,
        ViewRendererInterface $view,
    ) {
        parent::__construct($res, $view);
    }

    /** cache i18n par requête */
    private ?array $strings = null;

    /** @return array<string,string> */
    private function i18n(): array
    {
        return $this->strings ??= TranslationLoader::load(defaultLang: 'fr');
    }

    /** @return array{id?:int,username?:string,role?:string,email?:string} */
    private function me(): array
    {
        return CurrentUserProvider::getUser() ?? [];
    }

    /** @return list<array{title:string,url:string,icon:string}> */
    private function sidebar(bool $isAdmin): array
    {
        return $this->links->get($isAdmin);
    }

    /** payload commun au shell dashboard
     *  @param array<string,mixed> $extra
     *  @return array<string,mixed>
     */
    private function base(array $extra = []): array
    {
        $user = $this->me();
        $isAdmin = ($user['role'] ?? null) === 'admin';

        $base = [
            'showHeader' => false,
            'showFooter' => false,
            'isDashboard' => true,
            'str' => $this->i18n(),
            'user' => $user,
            'isAdmin' => $isAdmin,
            'links' => $this->sidebar($isAdmin),
            'flash' => FlashBag::consume(),
        ];

        return array_replace($base, $extra);
    }

    /** champ CSRF “trusted HTML” */
    private function csrfInput(): string
    {
        return CsrfTokenManager::insertInput();
    }

    /* ---------------- Routes (GET) ---------------- */

    #[Route(path: '', methods: ['GET'])]
    public function index(): Response
    {
        return $this->page('dashboard:home', $this->base([
            'title' => 'Dashboard',
        ]));
    }

    #[Route(path: '/users', methods: ['GET'])]
    public function users(): Response
    {
        $errors = FormState::consumeErrors();
        $prefill = FormState::consumeData();

        return $this->page('dashboard:home', $this->base([
            'title' => 'Utilisateurs',
            'component' => 'dashboard/dash_users',
            'users' => $this->users->getAllUsers(),
            'errors' => $errors,
            'prefill' => $prefill,
            'createAction' => '/dashboard/users/create',
            'deleteAction' => '/dashboard/users/delete',
            'csrf_input' => $this->csrfInput(),
        ]));
    }

    #[Route(path: '/account', methods: ['GET'])]
    public function account(): Response
    {
        $errors = FormState::consumeErrors();
        $prefill = FormState::consumeData();

        return $this->page('dashboard:home', $this->base([
            'title' => 'Mon compte',
            'component' => 'dashboard/dash_account',
            'errors' => $errors,
            'prefill' => $prefill,
            'action' => '/dashboard/account/password',
            'editUserAction' => '/dashboard/account/update',
            'csrf_input' => $this->csrfInput(),
        ]));
    }

    #[Route(path: '/agenda', methods: ['GET'])]
    public function agenda(): Response
    {
        return $this->page('dashboard:home', $this->base([
            'title' => 'Mon agenda',
            'component' => 'dashboard/dash_agenda',
        ]));
    }
    /* ---------------- Actions (POST) ---------------- */

    #[Route(path: '/account/password', methods: ['POST'])]
    public function accountPassword(): Response
    {
        CsrfTokenManager::requireValidToken();

        $userId = (int) (($this->me()['id'] ?? 0));
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
                return Redirect::withSuccess('/dashboard/account', 'Mot de passe modifié avec succès.');
            }
            $errors = $svcErrors ?: ['_global' => 'Échec de la modification du mot de passe.'];
        }

        return Redirect::withErrors(
            '/dashboard/account',
            'Le formulaire contient des erreurs.',
            $errors,
            [] // pas de pré-remplissage sensible ici
        );
    }
}
