<?php

declare(strict_types=1);

namespace App\Controller;

use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Contracts\ViewRendererInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Routing\Attribute\Route;
use Capsule\Routing\Attribute\RoutePrefix;
use Capsule\Security\Authenticator;
use Capsule\Security\CsrfTokenManager;
use Capsule\View\BaseController;
use PDO;

#[RoutePrefix('')]
final class LoginController extends BaseController
{
    public function __construct(
        private PDO $pdo,
        ResponseFactoryInterface $res,
        ViewRendererInterface $view
    ) {
        parent::__construct($res, $view);
    }

    /** GET /login — affiche le formulaire */
    #[Route(path: '/login', methods: ['GET'])]
    public function loginForm(Request $req): Response
    {
        $errors = \Capsule\Http\Support\FormState::consumeErrors();
        $prefill = \Capsule\Http\Support\FormState::consumeData();

        return $this->page('admin:login', [     // <-- ICI
            'showHeader' => true,
            'showFooter' => true,
            'title' => 'Connexion',
            'str' => $this->translations(),
            'error' => $errors['_global'] ?? null,
            'errors' => $errors,
            'prefill' => $prefill,
            'csrf_input' => $this->csrfInput(), // {{{csrf_input}}}
            'action' => '/login',
        ]);
    }

    /** POST /login — traite la soumission */
    #[Route(path: '/login', methods: ['POST'])]
    public function loginSubmit(Request $req): Response
    {
        if (strtoupper($req->method) !== 'POST') {
            return $this->res->redirect('/login', 303);
        }

        CsrfTokenManager::requireValidToken();

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            \Capsule\Http\Support\FormState::set(['_global' => 'Champs requis manquants.'], ['username' => $username]);
            \Capsule\Http\Support\FlashBag::add('error', 'Le formulaire contient des erreurs.');

            return $this->res->redirect('/login', 303); // PRG
        }

        $success = Authenticator::login($this->pdo, $username, $password);

        if ($success) {
            return $this->res->redirect('/dashboard/account', 302);
        }

        \Capsule\Http\Support\FormState::set(['_global' => 'Identifiants incorrects.'], ['username' => $username]);
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
