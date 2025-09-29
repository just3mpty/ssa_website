<?php

declare(strict_types=1);

// Autoload
require dirname(__DIR__) . '/vendor/autoload.php';

use Capsule\Http\Message\Request;
use Capsule\Http\Emitter\SapiEmitter;
use Capsule\Kernel\KernelHttp;
use Capsule\Auth\PhpSessionReader;
use Capsule\Http\Middleware\{
    ErrorBoundary,
    SecurityHeaders,
    AuthRequiredMiddleware,
    RequiredRoleMiddleware
};

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
$session = new PhpSessionReader();

$middlewares = [
    new ErrorBoundary(debug: (bool)($_ENV['APP_DEBUG'] ?? false)),
    new SecurityHeaders(),

    // Protège tout /dashboard sauf /login et /logout
    new AuthRequiredMiddleware(
        session: $session,
        protectedPrefix: '/dashboard',
        whitelist: ['/login','/logout'],
        redirectTo: '/login'
    ),

    // Vérifie que l'utilisateur a le rôle "admin" sur /dashboard
    new RequiredRoleMiddleware(
        session: $session,
        requiredRole: 'admin',
        protectedPrefix: '/dashboard',
        whitelist: ['/login','/logout'],
        redirectTo: '/login'
    ),
];

// Kernel = orchestration pure (pas d’I/O)
$kernel = new KernelHttp($middlewares, $router);

// Exécuter la pipeline
$response = $kernel->handle($request);

// Émettre la réponse (I/O unique)
$emitter = new SapiEmitter();
$emitter->emit($response);
