<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Framework\AbstractController;
use CapsuleLib\Service\Database\SqliteConnection;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use CapsuleLib\Security\Authenticator;

class AdminController extends AbstractController
{
    /**
     * GET /login
     * Affiche le formulaire de connexion
     */
    public function loginForm(): void
    {
        echo $this->renderView('admin/login.php', [
            'title' => 'Connexion',
            'error' => null,
        ]);
    }

    /**
     * POST /login
     * Traite la soumission du formulaire de connexion
     */
    public function loginSubmit(): void
    {
        $pdo = SqliteConnection::getInstance();
        $success = Authenticator::login($pdo, $_POST['username'], $_POST['password']);

        if ($success) {
            header('Location: /dashboard');
            exit;
        }

        // Si erreur, ré-affiche formulaire avec message
        echo $this->renderView('admin/login.php', [
            'title' => 'Connexion',
            'error' => 'Identifiants incorrects.',
        ]);
    }

    /**
     * GET /dashboard
     * Tableau de bord protégé (admin)
     */
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

    /**
     * GET /logout
     */
    public function logout(): void
    {
        Authenticator::logout();
        header('Location: /login');
        exit;
    }
}
