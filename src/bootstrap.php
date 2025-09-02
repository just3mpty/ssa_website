<?php

declare(strict_types=1);

use CapsuleLib\Core\DIContainer;
use CapsuleLib\Core\LoginController;
use CapsuleLib\Routing\Router;
use CapsuleLib\Database\MariaDBConnection;
use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use App\Controller\HomeController;
use App\Controller\DashboardController;
use App\Controller\ArticleController;
use CapsuleLib\Repository\UserRepository;
use CapsuleLib\Service\UserService;

require_once dirname(__DIR__) . '/lib/Helper/html_secure.php';

/**
 * Configuration de l'injection de dépendances et définition des routes.
 *
 * Cette configuration :
 * - Initialise le container DI avec les services et contrôleurs.
 * - Définit la table des routes HTTP avec leurs handlers associés.
 * - Instancie et prépare un routeur pour dispatcher les requêtes.
 *
 * @return Router Le routeur configuré prêt à dispatcher la requête HTTP courante.
 */

// Instanciation du container d'injection de dépendances
$container = new DIContainer();

// Définition des dépendances de base (ex : PDO)
$container->set(
    'pdo',
    fn() => MariaDBConnection::getInstance()
);

// Définition des services et contrôleurs publics
$container->set('articleRepository', fn($c) => new ArticleRepository($c->get('pdo')));
$container->set('articleService', fn($c) => new ArticleService($c->get('articleRepository')));
$container->set('homeController', fn($c) => new HomeController($c->get('articleService')));
$container->set('articleController', fn($c) => new ArticleController($c->get('articleService')));
$container->set('userRepository', fn($c) => new UserRepository($c->get('pdo')));

// Définition du contrôleur admin (accès restreint)
$container->set('loginController', fn($c) => new LoginController($c->get('pdo')));
// Définition des services et contrôleurs privés (ex : authentification)
$container->set('userService', fn($c) => new UserService($c->get('userRepository')));
$container->set(
    'passwords',
    fn($c) =>
    new \CapsuleLib\Service\PasswordService($c->get('userRepository'), 8, [])
);

$container->set(
    'dashboardController',
    fn($c) =>
    new DashboardController(
        $c->get('userService'),
        $c->get('articleService'),
        $c->get('passwords'),
    )
);
$container->set(
    'articlesAdminController',
    fn($c) =>
    new \App\Controller\ArticlesAdminController($c->get('articleService'))
);


// --- Aliases pour éviter répétition ---
$aa = $container->get('articlesAdminController');
$hc = $container->get('homeController');
$ac = $container->get('articleController');
$lc = $container->get('loginController');
$dc = $container->get('dashboardController');

// Déclaration des routes : méthode HTTP, chemin, et handler (contrôleur + méthode)
$routes = [
    // Public
    ['GET',  '/',        [$hc, 'home']],
    ['GET',  '/projet',  [$hc, 'projet']],
    ['GET',  '/galerie', [$hc, 'galerie']],
    ['GET',  '/wiki',    [$hc, 'wiki']],

    // Auth
    ['GET',  '/login',  [$lc, 'loginForm']],
    ['POST', '/login',  [$lc, 'loginSubmit']],
    ['GET',  '/logout', [$lc, 'logout']],

    // Dashboard
    ['GET',  '/dashboard/home',             [$dc, 'home']],
    ['GET',  '/dashboard/account',          [$dc, 'account']],
    ['POST', '/dashboard/account/password', [$dc, 'accountPassword']],
    ['GET',  '/dashboard/users',            [$dc, 'users']],
    ['POST', '/dashboard/users/create',     [$dc, 'usersCreate']],
    ['POST', '/dashboard/users/delete',     [$dc, 'usersDelete']],
    ['GET',  '/dashboard/articles',         [$dc, 'articles']],

    // Articles
    ['GET',   '/articles',             [$ec, 'listArticles']],
    ['GET',   '/articles/create',      [$ec, 'createForm']],
    ['POST',  '/articles/create',      [$ec, 'createSubmit']],
    ['GET',   '/articles/edit/{id}',   [$ec, 'editForm']],
    ['POST',  '/articles/edit/{id}',   [$ec, 'editSubmit']],
    ['POST',  '/articles/delete/{id}', [$ec, 'deleteSubmit']],

    // Dashboard articles (admin)
    ['GET',  '/dashboard/articles',             [$aa, 'index']],
    ['GET',  '/dashboard/articles/create',      [$aa, 'createForm']],
    ['POST', '/dashboard/articles/create',      [$aa, 'createSubmit']],
    ['GET',  '/dashboard/articles/edit/{id}',   [$aa, 'editForm']],
    ['POST', '/dashboard/articles/edit/{id}',   [$aa, 'editSubmit']],
    ['POST', '/dashboard/articles/delete/{id}', [$aa, 'deleteSubmit']],
];
// Instanciation et configuration du routeur HTTP
$router = new Router();
foreach ($routes as [$method, $path, $handler]) {
    $router->{strtolower($method)}($path, $handler);
}
$router->setNotFoundHandler(function () {
    http_response_code(404);
    echo "404 Not Found";
});

// Retourne l'instance du routeur prêt à dispatcher la requête
return $router;
