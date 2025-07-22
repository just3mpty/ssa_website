<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use CapsuleLib\Security\Authenticator;
use CapsuleLib\Security\CsrfTokenManager;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use App\Lang\Translate;
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
        Translate::detect_and_load(default: 'fr', page: basename($_SERVER['SCRIPT_NAME'], '.php'));

        return [
            'lang'              => $_SESSION['lang'] ?? 'fr',
            'meta_description' => Translate::action('meta_description'),
            'meta_keywords'    => Translate::action('meta_keywords'),
            'meta_author'      => Translate::action('meta_author'),
            'page_title'       => Translate::action('page_title'),
            'nav_title'        => Translate::action('nav_title'),
            'nav_home'         => Translate::action('nav_home'),
            'nav_apropos'      => Translate::action('nav_apropos'),
            'nav_actualites'   => Translate::action('nav_actualites'),
            'nav_agenda'       => Translate::action('nav_agenda'),
            'nav_project'      => Translate::action('nav_project'),
            'nav_galerie'      => Translate::action('nav_galerie'),
            'nav_contact'      => Translate::action('nav_contact'),
            'lang_fr'          => Translate::action('lang_fr'),
            'lang_br'          => Translate::action('lang_br'),
            'footer_address'   => Translate::action('footer_address'),
            'footer_tel'       => Translate::action('footer_tel'),
            'footer_email_1'   => Translate::action('footer_email_1'),
            'footer_email_2'   => Translate::action('footer_email_2'),
            'footer_siret'     => Translate::action('footer_siret'),
            'footer_copyright' => Translate::action('footer_copyright'),
            // ADMIN
            'dashboard_title' => Translate::action('dashboard_title'),
            'welcome'         => Translate::action('welcome'),

            'login_title'    => Translate::action('login_title'),
            'login_username' => Translate::action('login_username'),
            'login_password' => Translate::action('login_password'),
            'login_submit'   => Translate::action('login_submit'),
            // ADMIN DASHBOARD (ajouts nécessaires pour compléter la vue)
            'admin_manage_events'  => Translate::action('admin_manage_events'),
            'admin_contacts'       => Translate::action('admin_contacts'),
            'admin_create_event'   => Translate::action('admin_create_event'),
            'logout'               => Translate::action('logout'),
        ];
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
