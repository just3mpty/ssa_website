<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use CapsuleLib\Router\Router;
use CapsuleLib\Http\SecureHeaders;

require dirname(__DIR__) . '/lib/autoload.php';

// 1. En-têtes HTTP sécurisés
SecureHeaders::send();

// 3. Démarrage du routeur
new Router();
