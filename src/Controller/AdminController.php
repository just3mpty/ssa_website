<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use CapsuleLib\Security\Authenticator;
use CapsuleLib\Security\CsrfTokenManager;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use PDO;

class AdminController extends RenderController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function loginForm(): void
    {
        echo $this->renderView('admin/login.php', [
            'title' => 'Connexion',
            'error' => null,
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
        ]);
    }

    public function logout(): void
    {
        Authenticator::logout();
        header('Location: /login');
        exit;
    }
}
