<?php

declare(strict_types=1);

use CapsuleLib\Core\DIContainer;
use CapsuleLib\Routing\Router;
use CapsuleLib\Database\Connection\MariaDBConnection;
use App\Repository\EventRepository;
use App\Service\EventService;
use App\Controller\HomeController;
use App\Controller\DashboardController;
use App\Controller\AdminController;
use App\Controller\EventController;
use CapsuleLib\Database\Repository\UserRepository;
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

// Définition des services et contrôleurs privés (ex : authentification)
$container->set('userService', fn($c) => new UserService($c->get('userRepository')));

// Définition du contrôleur admin (accès restreint)
$container->set('adminController', fn($c) => new AdminController($c->get('pdo')));
$container->set('dashboardController', fn($c) => new DashboardController($c->get('userService'), $c->get('eventService')));

// Déclaration des routes : méthode HTTP, chemin, et handler (contrôleur + méthode)
$routes = [
    ['GET',  '/',             [$container->get('homeController'), 'home']],
    ['GET',  '/projet',       [$container->get('homeController'), 'projet']],
    ['GET',  '/galerie',      [$container->get('homeController'), 'galerie']],
    ['GET',  '/wiki',         [$container->get('homeController'), 'wiki']],

    // Auth
    ['GET', '/login',        [$container->get('adminController'), 'loginForm']],
    ['POST', '/login',       [$container->get('adminController'), 'loginSubmit']],
    ['GET', '/logout',       [$container->get('adminController'), 'logout']],

    // Dashboard pages
    ['GET', '/dashboard/home',  [$container->get('dashboardController'), 'home']],
    ['GET', '/dashboard/account', [$container->get('dashboardController'), 'account']],
    ['GET', '/dashboard/users',   [$container->get('dashboardController'), 'users']],
    ['GET', '/dashboard/articles',   [$container->get('dashboardController'), 'articles']],

    ['GET',   '/events',                [$container->get('eventController'), 'listEvents']],
    ['GET',   '/events/create',         [$container->get('eventController'), 'createForm']],
    ['POST',  '/events/create',         [$container->get('eventController'), 'createSubmit']],
    ['GET',   '/events/edit/{id}',      [$container->get('eventController'), 'editForm']],
    ['POST',  '/events/edit/{id}',      [$container->get('eventController'), 'editSubmit']],
    ['POST',  '/events/delete/{id}',    [$container->get('eventController'), 'deleteSubmit']],
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
