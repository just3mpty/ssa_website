<?php

declare(strict_types=1);

use Capsule\Infrastructure\Container\DIContainer;
use Capsule\Routing\RouterHandler;
use Capsule\Routing\ControllerInvoker;
use Capsule\Routing\RouteScanner;

require_once dirname(__DIR__) . '/src/Support/html_secure.php';

/** 1) Container */
$container = require dirname(__DIR__) . '/config/container.php';
if (!$container instanceof DIContainer) {
    throw new RuntimeException('config/container.php must return a DIContainer instance.');
}

/** 2) Router (notre implémentation) */
$router = new RouterHandler();

/** 3) Brancher le container dans l'invoker */
ControllerInvoker::setContainer($container);

/** 4) Découverte auto des contrôleurs dans app/Controller/*Controller.php */
$controllers = [];
$baseDir = dirname(__DIR__) . '/app/Controller';
$files = glob($baseDir . '/*Controller.php') ?: [];

foreach ($files as $file) {
    $basename = basename($file, '.php');             // ex: UserController
    $fqcn = 'App\\Controller\\' . $basename;         // ex: App\Controller\UserController

    // Vérifie que la classe est autoloadable avant d’enregistrer
    if (!class_exists($fqcn)) {
        // Si ça ne charge pas, tente un require_once en secours
        require_once $file;
    }
    if (!class_exists($fqcn)) {
        // On ignore poliment si la classe n’existe toujours pas
        continue;
    }

    $controllers[] = $fqcn;
}

/** 5) Enregistrer les routes via attributs (liste auto) */
if ($controllers) {
    RouteScanner::register($controllers, $router);
} else {
    // Optionnel: tu peux lever ou logger — je laisse un guard explicite
    // throw new RuntimeException('Aucun contrôleur trouvé dans app/Controller/*.');
}

/** 6) Retourner le router configuré */
return $router;
