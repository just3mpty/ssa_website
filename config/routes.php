<?php

declare(strict_types=1);

use App\Controller\MainController;
use App\Controller\AdminController;

const ROUTES = [

    //AdminController
    '/dashboard' => [
        'controller' => AdminController::class,
        'method'     => 'dashboard',
    ],
    '/login' => [
        'controller' => AdminController::class,
        'method'     => 'login',
    ],

    // MainController
    '/' => [
        'controller' => MainController::class,
        'method'     => 'home',
    ],
    '/projet' => [
        'controller' => MainController::class,
        'method'     => 'projet',
    ],
    '/participer' => [
        'controller' => MainController::class,
        'method'     => 'participer',
    ],
    '/actualites' => [
        'controller' => MainController::class,
        'method'     => 'actualites',
    ],
    '/galerie' => [
        'controller' => MainController::class,
        'method'     => 'galerie',
    ],
    '/apropos' => [
        'controller' => MainController::class,
        'method'     => 'apropos',
    ],
    '/contact' => [
        'controller' => MainController::class,
        'method'     => 'contact',
    ],
    '/wiki' => [
        'controller' => MainController::class,
        'method'     => 'wiki',
    ],
];
