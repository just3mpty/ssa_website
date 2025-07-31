<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use CapsuleLib\Security\Authenticator;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use App\Lang\TranslationLoader;

/**
 * Contrôleur dédié au tableau de bord admin (structure, sous-pages, widgets).
 * Toutes les routes internes "dashboard" passent ici.
 */
class DashboardController extends RenderController
{
    /**
     * Charge les chaînes de traduction pour la page courante.
     *
     * @return array<string, string>
     */
    private function getStrings(): array
    {
        return TranslationLoader::load(defaultLang: 'fr');
    }

    /**
     * Page d'accueil du dashboard admin.
     */
    public function home(): void
    {
        AuthMiddleware::handle();
        $user = Authenticator::getUser();
        $isAdmin = ($user['role'] ?? null) === 'admin';
        $links = [
            ['title' => 'Mon compte',     'url' => 'account',  'icon' => 'account'],
            ['title' => 'Utilisateurs',   'url' => 'users',    'icon' => 'users'],
            ['title' => 'Mes articles',   'url' => 'articles', 'icon' => 'articles'],
            ['title' => 'Accueil',        'url' => 'index',                'icon' => 'home'],
            ['title' => 'Déconnexion',    'url' => 'logout',               'icon' => 'logout'],
        ];


        // $dashboardContent = $this->renderComponent('dashboard/home.php', [...]); // Si tu utilises des composants partiels

        echo $this->renderView('dashboard/home.php', [
            'title'    => 'Dashboard',
            'isDashboard' => true,
            'links' => $links,
            'user'     => $user,
            'isAdmin'  => $isAdmin,
            'username' => $user['username'] ?? '',
            'dashboardContent' => null, // ou $dashboardContent si tu veux du contenu variable
            'str'      => $this->getStrings(),
        ]);
    }

    /**
     * Page "Mon compte" dans le dashboard.
     */
    public function account(): void
    {
        AuthMiddleware::handle();
        $user = Authenticator::getUser();
        $isAdmin = ($user['role'] ?? null) === 'admin';

        $links = [
            ['title' => 'Mon compte',     'url' => 'account',  'icon' => 'account'],
            ['title' => 'Utilisateurs',   'url' => 'users',    'icon' => 'users'],
            ['title' => 'Mes articles',   'url' => 'articles', 'icon' => 'articles'],
            ['title' => 'Accueil',        'url' => 'index',                'icon' => 'home'],
            ['title' => 'Déconnexion',    'url' => 'logout',               'icon' => 'logout'],
        ];

        echo $this->renderView('dashboard/home.php', [
            'title'    => 'Mon compte',
            'isDashboard' => true,
            'user'     => $user,
            'links' => $links,
            'isAdmin'  => $isAdmin,
            'username' => $user['username'] ?? '',
            'dashboardContent' => $this->renderComponent('account.php', [
                'user' => $user,
                'str'  => $this->getStrings(),
            ]),
            'str'      => $this->getStrings(),
        ]);
    }

    /**
     * Page "Utilisateurs" dans le dashboard.
     */
    public function users(): void
    {
        AuthMiddleware::requireRole('admin');

        $links = [
            ['title' => 'Mon compte',     'url' => 'account',  'icon' => 'account'],
            ['title' => 'Utilisateurs',   'url' => 'users',    'icon' => 'users'],
            ['title' => 'Mes articles',   'url' => 'articles', 'icon' => 'articles'],
            ['title' => 'Accueil',        'url' => 'index',                'icon' => 'home'],
            ['title' => 'Déconnexion',    'url' => 'logout',               'icon' => 'logout'],
        ];
        // Récupérer la liste des users, etc.
        // $users = ...;
        echo $this->renderView('dashboard/home.php', [
            'title'    => 'Utilisateurs',
            'isDashboard' => true,
            'links' => $links,
            'isAdmin'  => true,
            'dashboardContent' => $this->renderComponent('users.php', [
                // 'users' => $users,
                'str' => $this->getStrings(),
            ]),
            'str'      => $this->getStrings(),
        ]);
    }

    /**
     * Page "Mes articles" dans le dashboard.
     */
    public function articles(): void
    {
        AuthMiddleware::handle();
        $user = Authenticator::getUser();
        echo $this->renderView('admin/dashboard.php', [
            'title'    => 'Mes articles',
            'isDashboard' => true,
            'user'     => $user,
            'dashboardContent' => $this->renderComponent('dashboard/articles.php', [
                // 'articles' => $articles,
                'str' => $this->getStrings(),
            ]),
            'str'      => $this->getStrings(),
        ]);
    }

    /**
     * Accueil général (peut aussi servir pour stats, widgets…).
     */
    public function index(): void
    {
        $this->home();
    }
}
