<?php
// bootstrap/app.php
declare(strict_types=1);

use CapsuleLib\Routing\Router;

require_once dirname(__DIR__) . '/lib/Helper/html_secure.php';

/** @var \CapsuleLib\Core\DIContainer $container */
$container = require dirname(__DIR__) . '/config/container.php';

/** @var callable $routesFactory */
$routesFactory = require dirname(__DIR__) . '/config/routes.php';
$routes = $routesFactory($container);

// Instanciation du routeur
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
