<?php

declare(strict_types=1);

use CapsuleLib\Core\DIContainer;
use CapsuleLib\Routing\Router;
use CapsuleLib\Database\MariaDBConnection;
use App\Repository\EventRepository;
use App\Service\EventService;
use App\Controller\HomeController;
use App\Controller\DashboardController;
use App\Controller\AdminController;
use App\Controller\EventController;
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
$container->set('eventRepository', fn($c) => new EventRepository($c->get('pdo')));
$container->set('eventService', fn($c) => new EventService($c->get('eventRepository')));
$container->set('homeController', fn($c) => new HomeController($c->get('eventService')));
$container->set('eventController', fn($c) => new EventController($c->get('eventService')));
$container->set('userRepository', fn($c) => new UserRepository($c->get('pdo')));

// Définition du contrôleur admin (accès restreint)
$container->set('adminController', fn($c) => new AdminController($c->get('pdo')));
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
        $c->get('eventService'),
        $c->get('passwords'),
    )
);

// --- Aliases pour éviter répétition ---
$hc = $container->get('homeController');
$ec = $container->get('eventController');
$ac = $container->get('adminController');
$dc = $container->get('dashboardController');

// Déclaration des routes : méthode HTTP, chemin, et handler (contrôleur + méthode)
$routes = [
    // Public
    ['GET',  '/',        [$hc, 'home']],
    ['GET',  '/projet',  [$hc, 'projet']],
    ['GET',  '/galerie', [$hc, 'galerie']],
    ['GET',  '/wiki',    [$hc, 'wiki']],

    // Auth
    ['GET',  '/login',  [$ac, 'loginForm']],
    ['POST', '/login',  [$ac, 'loginSubmit']],
    ['GET',  '/logout', [$ac, 'logout']],

    // Dashboard
    ['GET',  '/dashboard/home',             [$dc, 'home']],
    ['GET',  '/dashboard/account',          [$dc, 'account']],
    ['POST', '/dashboard/account/password', [$dc, 'accountPassword']],
    ['GET',  '/dashboard/users',            [$dc, 'users']],
    ['POST', '/dashboard/users/create',     [$dc, 'usersCreate']],
    ['POST', '/dashboard/users/delete',     [$dc, 'usersDelete']],
    ['GET',  '/dashboard/articles',         [$dc, 'articles']],

    // Events
    ['GET',   '/events',             [$ec, 'listEvents']],
    ['GET',   '/events/create',      [$ec, 'createForm']],
    ['POST',  '/events/create',      [$ec, 'createSubmit']],
    ['GET',   '/events/edit/{id}',   [$ec, 'editForm']],
    ['POST',  '/events/edit/{id}',   [$ec, 'editSubmit']],
    ['POST',  '/events/delete/{id}', [$ec, 'deleteSubmit']],
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
