<?php

declare(strict_types=1);

// --- Autoload (temporaire). Idéalement: require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__) . '/src/Autoload.php';

use Capsule\Http\Message\Request;
use Capsule\Http\Emitter\SapiEmitter;
use Capsule\Kernel\KernelHttp;
use Capsule\Auth\PhpSessionReader;
use Capsule\Http\Middleware\{
    ErrorBoundary,
    SecurityHeaders,
    AuthRequiredMiddleware,
};
use Capsule\Routing\RouterHandler;

// Sécurité session/env (évite l’I/O applicatif ici)
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Récupère le Router préparé par le bootstrap (AUCUNE I/O)
$bootstrapPath = dirname(__DIR__) . '/bootstrap/app.php';
/** @var RouterHandler $router */
$router = require $bootstrapPath;

// Construire la Request depuis les globals
$request = Request::fromGlobals();
$session = new PhpSessionReader();

$middlewares = [
    // ErrorBoundary doit mapper nos exceptions :
    //  - NotFound         -> 404
    //  - MethodNotAllowed -> 405 + header Allow
    new ErrorBoundary(debug: (bool)($_ENV['APP_DEBUG'] ?? true)),

    new SecurityHeaders(),

    new AuthRequiredMiddleware(
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
