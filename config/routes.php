<?php

declare(strict_types=1);

use App\Controller\MainController;
use App\Controller\AdminController;
use App\Controller\EventController;

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
    '/logout' => [
        'controller' => AdminController::class,
        'method' => 'logout'
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

    // EventController
    '/events' => [
        'controller' => EventController::class,
        'method' => 'listEvents',
    ],
    '/events/create' => [
        'controller' => EventController::class,
        'method' => 'createEvent',
    ],
    '/events/edit/{id}' => [
        'controller' => EventController::class,
        'method' => 'editEvent',
    ],
    '/events/delete/{id}' => [
        'controller' => EventController::class,
        'method' => 'deleteEvent',
    ],
];
