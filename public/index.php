<?php

declare(strict_types=1);

use CapsuleLib\Http\SecureHeaders;

require dirname(__DIR__) . '/lib/autoload.php';

ini_set('session.cookie_httponly', '1');
//ini_set('session.cookie_secure', '1'); // uniquement si tu es en HTTPS
ini_set('session.cookie_samesite', 'Strict');
// Affiche les erreurs en dev
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

error_reporting(E_ALL);

// Démarre la session dès le début
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 1. Sécurité HTTP
SecureHeaders::send();

// Charge bootstrap (instancie tout et retourne $router prêt)
$router = require dirname(__DIR__) . '/src/bootstrap.php';

// Lance le dispatch (analyse la requête et appelle le contrôleur)
$router->dispatch();
