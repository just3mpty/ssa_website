<?php

declare(strict_types=1);

use CapsuleLib\Framework\Container;
use CapsuleLib\Service\Database\MariaDBConnection;
use CapsuleLib\Router\Router;
use App\Model\Event;
use App\Service\EventService;
use App\Controller\HomeController;
use App\Controller\AdminController;
use App\Controller\EventController;

require dirname(__DIR__) . '/lib/Helpers/h_specialchar.php';

// Instanciation du container
$container = new Container();

// Définition des dépendances
$container->set(
    'pdo',
    fn() => MariaDBConnection::getInstance()
);

// Public Container
$container->set('eventModel', fn($c) => new Event($c->get('pdo')));
$container->set('eventService', fn($c) => new EventService($c->get('eventModel')));
$container->set('homeController', fn($c) => new HomeController($c->get('eventService')));
$container->set('eventController', fn($c) => new EventController($c->get('eventService')));

// Admin Container
$container->set('adminController', fn($c) => new AdminController($c->get('pdo')));

// Table des routes : instances et méthodes
$routes = [
    ['GET',  '/',             [$container->get('homeController'), 'home']],
    ['GET',  '/projet',       [$container->get('homeController'), 'projet']],
    ['GET',  '/galerie',      [$container->get('homeController'), 'galerie']],
    ['GET',  '/wiki',         [$container->get('homeController'), 'wiki']],

    ['GET',  '/login',        [$container->get('adminController'), 'loginForm']],
    ['POST', '/login',        [$container->get('adminController'), 'loginSubmit']],
    ['GET',  '/dashboard',    [$container->get('adminController'), 'dashboard']],
    ['GET',  '/logout',       [$container->get('adminController'), 'logout']],

    ['GET',   '/events',                [$container->get('eventController'), 'listEvents']],
    ['GET',   '/events/create',         [$container->get('eventController'), 'createForm']],
    ['POST',  '/events/create',         [$container->get('eventController'), 'createSubmit']],
    ['GET',   '/events/edit/{id}',      [$container->get('eventController'), 'editForm']],
    ['POST',  '/events/edit/{id}',      [$container->get('eventController'), 'editSubmit']],
    ['POST',  '/events/delete/{id}',    [$container->get('eventController'), 'deleteSubmit']],
];

// Instancie le router
$router = new Router();
foreach ($routes as [$method, $path, $handler]) {
    $router->{strtolower($method)}($path, $handler);
}
$router->setNotFoundHandler(function () {
    http_response_code(404);
    echo "404 Not Found";
});

// On retourne le router prêt à dispatcher
return $router;
