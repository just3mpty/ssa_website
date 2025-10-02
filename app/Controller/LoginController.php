<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Contracts\ViewRendererInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Routing\Attribute\Route;
use Capsule\Security\Authenticator;
use Capsule\Security\CsrfTokenManager;
use Capsule\View\BaseController;
use PDO;

#[\Capsule\Routing\Attribute\RoutePrefix('')]
final class LoginController extends BaseController
{
    private ?array $strings = null;

    public function __construct(
        private PDO $pdo,
        ResponseFactoryInterface $res,
        ViewRendererInterface $view
    ) {
        parent::__construct($res, $view);
    }

    /** @return array<string,string> */
    private function strings(): array
    {
        return $this->strings ??= TranslationLoader::load(defaultLang: 'fr');
    }

    private function csrfInput(): string
    {
        ob_start();
        CsrfTokenManager::insertInput();    // génère l’input (HTML “trusted”)

        return (string) ob_get_clean();
    }

    /** GET /login — affiche le formulaire */
    #[Route(path: '/login', methods: ['GET'])]
    public function loginForm(Request $req): Response
    {
        // Récupération PRG (si tu utilises un store session pour erreurs/prefill)
        $errors = \Capsule\Http\Support\FormState::consumeErrors();
        $prefill = \Capsule\Http\Support\FormState::consumeData();

        return $this->html('admin/login.tpl.php', [
            'showHeader' => true,
            'showFooter' => true,
            'title' => 'Connexion',
            'str' => $this->strings(),

            // Form state
            'error' => $errors['_global'] ?? null,
            'errors' => $errors,
            'prefill' => $prefill,
            'csrf_input' => \Capsule\Security\CsrfTokenManager::insertInput(), // {{{csrf_input}}}
            'action' => '/login',             // {{action}}
        ]);
    }

    /** POST /login — traite la soumission */
    #[Route(path: '/login', methods: ['POST'])]
    public function loginSubmit(Request $req): Response
    {
        // Défense en profondeur (même si la route est limitée à POST)
        if (strtoupper($req->method) !== 'POST') {
            return $this->res->redirect('/login', 303);
        }

        // CSRF
        CsrfTokenManager::requireValidToken();

        // Inputs (si tu n’as pas un parser de body, on reste sur $_POST)
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            \Capsule\Http\Support\FormState::set(
                ['_global' => 'Champs requis manquants.'],
                ['username' => $username]
            );
            \Capsule\Http\Support\FlashBag::add('error', 'Le formulaire contient des erreurs.');

            return $this->res->redirect('/login', 303); // PRG
        }

        $success = Authenticator::login($this->pdo, $username, $password);

        if ($success) {
            // Redirection vers le dashboard
            return $this->res->redirect('/dashboard/account', 302);
        }

        // Échec → PRG avec message et préremplissage
        \Capsule\Http\Support\FormState::set(
            ['_global' => 'Identifiants incorrects.'],
            ['username' => $username]
        );
        \Capsule\Http\Support\FlashBag::add('error', 'Identifiants incorrects.');

        return $this->res->redirect('/login', 303);
    }

    /** GET/POST /logout — détruit la session et redirige */
    #[Route(path: '/logout', methods: ['GET', 'POST'])]
    public function logout(): Response
    {
        Authenticator::logout();

        return $this->res->redirect('/login', 302);
    }
}
