<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Framework\AbstractController;
use CapsuleLib\Service\Database\SqliteConnection;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use CapsuleLib\Security\Authenticator;

/**
 * Contrôleur principal du site.
 *
 * Gère les pages publiques statiques telles que l'accueil, les actualités, la galerie, etc.
 * Chaque méthode correspond à une route définie dans `config/routes.php`.
 */
class AdminController extends AbstractController
{

    public function login(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = SqliteConnection::getInstance();
            $success = Authenticator::login($pdo, $_POST['username'], $_POST['password']);

            if ($success) {
                header('Location: /dashboard');
                exit;
            }

            $error = "Identifiants incorrects.";
        }

        echo $this->renderView('admin/login.php', [
            'title' => 'Connexion',
            'error' => $error
        ]);
    }

    public function dashboard(): void
    {
        AuthMiddleware::handle(); // 🚫 Bloque si non connecté
        echo $this->renderView('admin/dashboard.php', ['title' => 'Accueil']);
    }
}
