<?php

declare(strict_types=1);

// Démarre la session dès le début
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Affiche les erreurs en dev
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require dirname(__DIR__) . '/lib/autoload.php';

use CapsuleLib\Http\SecureHeaders;
use CapsuleLib\Router\Router;

// 1. Sécurité HTTP
SecureHeaders::send();

// 2. Instanciation du routeur
$router = new Router();

// 3. Chargement des routes définies dans config/routes.php
$routes = require dirname(__DIR__) . '/config/routes.php';

// 4. Enregistrement des routes selon la méthode HTTP
foreach ($routes as [$method, $path, $handler]) {
    match (strtoupper($method)) {
        'GET'    => $router->get($path, $handler),
        'POST'   => $router->post($path, $handler),
        default  => $router->any($path, $handler),
    };
}

// 5. Gestion personnalisée de la 404 (optionnelle)
$router->setNotFoundHandler(function () {
    echo "🚫 Oups, page non trouvée (404)";
});

// 6. Lancement de la requête
$router->dispatch();
