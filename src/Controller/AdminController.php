<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use CapsuleLib\Security\Authenticator;
use CapsuleLib\Security\CsrfTokenManager;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use App\Lang\TranslationLoader;
use PDO;

class AdminController extends RenderController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function getStrings(): array
    {
        return TranslationLoader::load(defaultLang: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php'));
    }

    public function loginForm(): void
    {
        echo $this->renderView('admin/login.php', [
            'title' => 'Connexion',
            'error' => null,
            'str'   => $this->getStrings(),
        ]);
    }

    public function loginSubmit(): void
    {
        CsrfTokenManager::requireValidToken();
        $success = Authenticator::login(
            $this->pdo,
            $_POST['username'] ?? '',
            $_POST['password'] ?? ''
        );


        if ($success) {
            header('Location: /dashboard');
            exit;
        }

        echo $this->renderView('admin/login.php', [
            'title' => 'Connexion',
            'error' => 'Identifiants incorrects.',
            'str'   => $this->getStrings(),
        ]);
    }

    public function dashboard(): void
    {
        AuthMiddleware::handle();
        $user = Authenticator::getUser();
        $isAdmin = ($user['role'] ?? null) === 'admin';

        echo $this->renderView('admin/dashboard.php', [
            'title'   => 'Accueil',
            'isAdmin' => $isAdmin,
            'user'    => $user,
            'username' => $user['username'] ?? '',
            'str'   => $this->getStrings(),
        ]);
    }

    public function logout(): void
    {
        Authenticator::logout();
        header('Location: /login');
        exit;
    }
}
