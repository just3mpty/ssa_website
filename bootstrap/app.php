<?php

declare(strict_types=1);

use Capsule\Infrastructure\Container\DIContainer;
use Capsule\Http\Routing\Router;

require_once dirname(__DIR__) . '/src/Support/html_secure.php';

/** 1) Container */
$container = require dirname(__DIR__) . '/config/container.php';
if (!$container instanceof DIContainer) {
    throw new RuntimeException('config/container.php must return a DIContainer instance.');
}

/** 2) Router */
$router = new Router();

/**
 * 3) Charger l’enregistreur de routes
 *    Signature attendue : function (Router $router, DIContainer $c): void
 */
$registerRoutes = require dirname(__DIR__) . '/config/routes.php';
if (!is_callable($registerRoutes)) {
    throw new RuntimeException('config/routes.php must return a callable (Router, DIContainer) => void.');
}

/** 4) Enregistrer les routes */
$registerRoutes($router, $container);

/** 5) (Facultatif) NotFound géré côté ErrorBoundary.
 *   Si tu veux un fallback local au router, tu peux garder un handler :
 */
// $router->setNotFoundHandler(fn() => Response::text('404 Not Found', 404));

/** 6) Retourne le router configuré */
return $router;
