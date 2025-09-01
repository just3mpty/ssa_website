<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use CapsuleLib\Security\Authenticator;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use App\Lang\TranslationLoader;
use CapsuleLib\Service\UserService;
use App\Service\EventService;

/**
 * Contrôleur dédié au tableau de bord admin (structure, sous-pages, widgets).
 * Toutes les routes internes "dashboard" passent ici.
 */
class DashboardController extends RenderController
{

    /**
     * Service d'accès et manipulation des événements.
     */
    private UserService $userService;
    private EventService $eventService;

    /**
     * Constructeur.
     *
     * @param EventService $eventService Service pour manipuler les événements.
     */
    public function __construct(UserService $userService, EventService $eventService)
    {
        $this->userService = $userService;
        $this->eventService = $eventService;
    }


    /**
     * Charge les chaînes de traduction pour la page courante.
     *
     * @return array<string, string>
     */
    private function getStrings(): array
    {
        return TranslationLoader::load(defaultLang: 'fr');
    }

    private function getLinks(): array
    {
        $links = [
            ['title' => 'Accueil',        'url' => 'home',                'icon' => 'home'],
            ['title' => 'Utilisateurs',   'url' => 'users',    'icon' => 'users'],
            ['title' => 'Mes articles',   'url' => 'articles', 'icon' => 'articles'],
            ['title' => 'Mon compte',     'url' => 'account',  'icon' => 'account'],
            ['title' => 'Déconnexion',    'url' => '../logout',               'icon' => 'logout'],
        ];
        return  $links;
    }

    /**
     * Page d'accueil du dashboard admin.
     */
    public function home(): void
    {
        AuthMiddleware::handle();
        $user = Authenticator::getUser();
        $isAdmin = ($user['role'] ?? null) === 'admin';


        // $dashboardContent = $this->renderComponent('dashboard/home.php', [...]); // Si tu utilises des composants partiels

        echo $this->renderView('dashboard/home.php', [
            'title'    => 'Dashboard',
            'isDashboard' => true,
            'links' => $this->getLinks(),
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
        


        $this->getLinks();

        echo $this->renderView('dashboard/home.php', [
            'title'            => 'Mon compte',
            'isDashboard'      => true,
            'user'             => $user,
            'links'            => $this->getLinks(),
            'isAdmin'          => $isAdmin,
            'username'         => $user['username'] ?? '',
            'dashboardContent' => $this->renderComponent('dash_account.php', [
                'user'         => $user,
                'str'          => $this->getStrings(),
            ]),
            'str'              => $this->getStrings(),
        ]);
    }

    /**
     * Page "Utilisateurs" dans le dashboard.
     */
    public function users(): void
    {
        AuthMiddleware::requireRole('admin');
        $users = $this->userService->getAllUsers();

        // Récupérer la liste des users, etc.
        echo $this->renderView('dashboard/home.php', [
            'title'    => 'Utilisateurs',
            'isDashboard' => true,
            'links' => $this->getLinks(),
            'isAdmin'  => true,
            'dashboardContent' => $this->renderComponent('dash_users.php', [
                'users' => $users,
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
        $articles = $this->eventService->getAll();
        echo $this->renderView('dashboard/home.php', [
            'title'    => 'Mes articles',
            'isDashboard' => true,
            'links' => $this->getLinks(),
            'user'     => $user,
            'dashboardContent' => $this->renderComponent('dash_articles.php', [
                'articles' => $articles,
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
