<?php

declare(strict_types=1);

namespace App\Controller;

use CapsuleLib\Core\RenderController;
use CapsuleLib\Security\Authenticator;
use CapsuleLib\Http\Middleware\AuthMiddleware;
use App\Lang\TranslationLoader;
use CapsuleLib\Service\UserService;
use App\Service\EventService;

final class DashboardController extends RenderController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly EventService $eventService,
    ) {}

    /** Cache par requête */
    private ?array $strings = null;
    private ?array $links   = null;

    /** ---- DRY helpers ---- */
    private function strings(): array
    {
        return $this->strings ??= TranslationLoader::load(defaultLang: 'fr');
    }

    // TODO: Idéalement, injecter un UrlGenerator et une ACL pour filtrer par rôle. */
    private function links(bool $isAdmin): array
    {
        if ($this->links !== null) {
            return $this->links;
        }

        $links = [
            ['title' => 'Accueil',      'url' => 'home',     'icon' => 'home'],
            ['title' => 'Utilisateurs', 'url' => 'users',    'icon' => 'users'],
            ['title' => 'Mes articles', 'url' => 'articles', 'icon' => 'articles'],
            ['title' => 'Mon compte',   'url' => 'account',  'icon' => 'account'],
            ['title' => 'Déconnexion',  'url' => '../logout', 'icon' => 'logout'],
        ];

        if (!$isAdmin) {
            $links = array_values(array_filter($links, fn($l) => $l['url'] !== 'users'));
        }

        return $this->links = $links;
    }

    private function currentUser(): array
    {
        // TODO: itération B: injecter CurrentUserInterface plutôt que statique
        return Authenticator::getUser() ?? [];
    }

    private function isAdmin(array $user): bool
    {
        return ($user['role'] ?? null) === 'admin';
    }

    /** Base payload commun au layout Dashboard */
    private function basePayload(array $extra = []): array
    {
        $user    = $this->currentUser();
        $isAdmin = $this->isAdmin($user);

        $base = [
            'isDashboard' => true,
            'title'       => '',                 // surchargé par action
            'user'        => $user,
            'username'    => $user['username'] ?? '',
            'isAdmin'     => $isAdmin,
            'links'       => $this->links($isAdmin),
            'str'         => $this->strings(),
            'dashboardContent' => null,          // surchargé si besoin
        ];

        return array_replace($base, $extra);
    }

    /**
     * Point unique de rendu du dashboard.
     * - Applique Auth par défaut (override possible).
     * - Rend un composant partiel si fourni.
     */
    private function renderDashboard(
        string $title,
        ?string $component = null,
        array $componentVars = [],
        bool $requireAdmin = false,
        bool $requireAuth = true,
    ): void {
        if ($requireAdmin) {
            AuthMiddleware::requireRole('admin');
        } elseif ($requireAuth) {
            AuthMiddleware::handle();
        }

        $content = null;
        if ($component !== null) {
            // Le partiel reçoit déjà 'str' si nécessaire
            $componentVars += ['str' => $this->strings()];
            $content = $this->renderComponent($component, $componentVars);
        }

        echo $this->renderView('dashboard/home.php', $this->basePayload([
            'title'            => $title,
            'dashboardContent' => $content,
        ]));
    }

    /** ---- Actions minces (skinny controller) ---- */

    public function home(): void
    {
        $this->renderDashboard('Dashboard');
    }

    public function account(): void
    {
        $this->renderDashboard('Mon compte', 'dash_account.php', [
            'user' => $this->currentUser(),
        ]);
    }

    public function users(): void
    {
        $users = $this->userService->getAllUsers();
        $this->renderDashboard('Utilisateurs', 'dash_users.php', [
            'users' => $users,
        ], requireAdmin: true);
    }

    public function articles(): void
    {
        $articles = $this->eventService->getAll();
        $this->renderDashboard('Mes articles', 'dash_articles.php', [
            'articles' => $articles,
        ]);
    }

    public function index(): void
    {
        $this->home();
    }
}

// NOTE: ANCIENNE CLASS

    //
    // declare(strict_types=1);
    //
    // namespace App\Controller;
    //
    // use CapsuleLib\Core\RenderController;
    // use CapsuleLib\Security\Authenticator;
    // use CapsuleLib\Http\Middleware\AuthMiddleware;
    // use App\Lang\TranslationLoader;
    // use CapsuleLib\Service\UserService;
    // use App\Service\EventService;
    //
    // /**
    //  * Contrôleur dédié au tableau de bord admin (structure, sous-pages, widgets).
    //  * Toutes les routes internes "dashboard" passent ici.
    //  */
    // class DashboardController extends RenderController
    // {
    //
    //     /**
    //      * Service d'accès et manipulation des événements.
    //      */
    //     private UserService $userService;
    //     private EventService $eventService;
    //
    //     /**
    //      * Constructeur.
    //      *
    //      * @param EventService $eventService Service pour manipuler les événements.
    //      */
    //     public function __construct(UserService $userService, EventService $eventService)
    //     {
    //         $this->userService = $userService;
    //         $this->eventService = $eventService;
    //     }
    //
    //
    //     /**
    //      * Charge les chaînes de traduction pour la page courante.
    //      *
    //      * @return array<string, string>
    //      */
    //     private function getStrings(): array
    //     {
    //         return TranslationLoader::load(defaultLang: 'fr');
    //     }
    //
    //     private function getLinks(): array
    //     {
    //         $links = [
    //             ['title' => 'Accueil',        'url' => 'home',                'icon' => 'home'],
    //             ['title' => 'Utilisateurs',   'url' => 'users',    'icon' => 'users'],
    //             ['title' => 'Mes articles',   'url' => 'articles', 'icon' => 'articles'],
    //             ['title' => 'Mon compte',     'url' => 'account',  'icon' => 'account'],
    //             ['title' => 'Déconnexion',    'url' => '../logout',               'icon' => 'logout'],
    //         ];
    //         return  $links;
    //     }
    //
    //     /**
    //      * Page d'accueil du dashboard admin.
    //      */
    //     public function home(): void
    //     {
    //         AuthMiddleware::handle();
    //         $user = Authenticator::getUser();
    //         $isAdmin = ($user['role'] ?? null) === 'admin';
    //
    //
    //         // $dashboardContent = $this->renderComponent('dashboard/home.php', [...]); // Si tu utilises des composants partiels
    //
    //         echo $this->renderView('dashboard/home.php', [
    //             'title'    => 'Dashboard',
    //             'isDashboard' => true,
    //             'links' => $this->getLinks(),
    //             'user'     => $user,
    //             'isAdmin'  => $isAdmin,
    //             'username' => $user['username'] ?? '',
    //             'dashboardContent' => null, // ou $dashboardContent si tu veux du contenu variable
    //             'str'      => $this->getStrings(),
    //         ]);
    //     }
    //
    //     /**
    //      * Page "Mon compte" dans le dashboard.
    //      */
    //     public function account(): void
    //     {
    //         AuthMiddleware::handle();
    //         $user = Authenticator::getUser();
    //         $isAdmin = ($user['role'] ?? null) === 'admin';
    //
    //         $this->getLinks();
    //
    //         echo $this->renderView('dashboard/home.php', [
    //             'title'            => 'Mon compte',
    //             'isDashboard'      => true,
    //             'user'             => $user,
    //             'links'            => $this->getLinks(),
    //             'isAdmin'          => $isAdmin,
    //             'username'         => $user['username'] ?? '',
    //             'dashboardContent' => $this->renderComponent('dash_account.php', [
    //                 'user'         => $user,
    //                 'str'          => $this->getStrings(),
    //             ]),
    //             'str'              => $this->getStrings(),
    //         ]);
    //     }
    //
    //     /**
    //      * Page "Utilisateurs" dans le dashboard.
    //      */
    //     public function users(): void
    //     {
    //         AuthMiddleware::requireRole('admin');
    //
    //         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //             $username = $_POST['username'] ?? null;
    //             $password = $_POST['password'] ?? null;
    //             $email    = $_POST['email'] ?? null;
    //             $role     = $_POST['role'] ?? 'employee';
    //
    //             if ($username && $password && $email) {
    //                 $this->userService->createUser($username, $password, $email, $role);
    //             }
    //         }
    //
    //         $users = $this->userService->getAllUsers();
    //
    //         // Récupérer la liste des users, etc.
    //         echo $this->renderView('dashboard/home.php', [
    //             'title'    => 'Utilisateurs',
    //             'isDashboard' => true,
    //             'links' => $this->getLinks(),
    //             'isAdmin'  => true,
    //             'dashboardContent' => $this->renderComponent('dash_users.php', [
    //                 'users' => $users,
    //                 'str' => $this->getStrings(),
    //             ]),
    //             'str'      => $this->getStrings(),
    //         ]);
    //     }
    //
    //     /**
    //      * Page "Mes articles" dans le dashboard.
    //      */
    //     public function articles(): void
    //     {
    //         AuthMiddleware::handle();
    //         $user = Authenticator::getUser();
    //         $articles = $this->eventService->getAll();
    //
    //         foreach ($articles as &$article) {
    //             $author = $this->userService->getUserById($article->author_id);
    //             $article->author = $author->username ?? 'Inconnu';
    //         }
    //
    //         echo $this->renderView('dashboard/home.php', [
    //             'title'             => 'Mes articles',
    //             'isDashboard'       => true,
    //             'links'             => $this->getLinks(),
    //             'user'              => $user,
    //             'dashboardContent'  => $this->renderComponent('dash_articles.php', [
    //                 'articles'      => $articles,
    //                 'str'           => $this->getStrings(),
    //             ]),
    //             'str'               => $this->getStrings(),
    //         ]);
    //     }
    //
    //     /**
    //      * Accueil général (peut aussi servir pour stats, widgets…).
    //      */
    //     public function index(): void
    //     {
    //         $this->home();
    //     }
    //}
