<?php

declare(strict_types=1);

// Autoload
require dirname(__DIR__) . '/vendor/autoload.php';

use Capsule\Http\Message\Request;
use Capsule\Http\Emitter\SapiEmitter;
use Capsule\Kernel\Kernel;
use Capsule\Http\Middleware\ErrorBoundary;
use Capsule\Http\Middleware\SecurityHeaders;
use Capsule\Http\Middleware\AuthMiddleware;

// Sécurité session/env (si besoin, mais évite l’I/O ici aussi)
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Récupère le Router préparé par le bootstrap (AUCUNE I/O)
$bootstrapPath = dirname(__DIR__) . '/bootstrap/app.php';
/** @var \Capsule\Routing\Router $router */
$router = require $bootstrapPath;

// Construire la Request depuis les globals
$request = Request::fromGlobals();

// Middlewares (ordre conseillé)
$middlewares = [
    new ErrorBoundary(debug: (bool)($_ENV['APP_DEBUG'] ?? false)),
    new SecurityHeaders(),           // ajoute X-Content-Type-Options, CSP, etc.
    new AuthMiddleware(),            // si tu en as un (sinon enlève-le)
];

// Kernel = orchestration pure (pas d’I/O)
$kernel = new Kernel($middlewares, $router);

// Exécuter la pipeline
$response = $kernel->handle($request);

// Émettre la réponse (I/O unique)
$emitter = new SapiEmitter();
$emitter->emit($response);
