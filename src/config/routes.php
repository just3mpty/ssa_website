<?php

declare(strict_types=1);

use App\Controller\MainController;
use App\Controller\AdminController;
use App\Controller\EventController;

return [
    // FRONTEND
    ['GET', '/', [MainController::class, 'home']],
    ['GET', '/projet', [MainController::class, 'projet']],
    ['GET', '/participer', [MainController::class, 'participer']],
    ['GET', '/galerie', [MainController::class, 'galerie']],
    ['GET', '/wiki', [MainController::class, 'wiki']],

    // AUTH
    ['GET',  '/login',     [AdminController::class, 'loginForm']],
    ['POST', '/login',     [AdminController::class, 'loginSubmit']],
    ['GET',  '/dashboard', [AdminController::class, 'dashboard']],
    ['GET',  '/logout',    [AdminController::class, 'logout']],

    // BACKOFFICE (admin protégé)
    ['GET', '/dashboard', [AdminController::class, 'dashboard']],

    // EVENTS
    ['GET',  '/events',                [EventController::class, 'listEvents']],
    ['GET',  '/events/create',         [EventController::class, 'createForm']],
    ['POST', '/events/create',         [EventController::class, 'createSubmit']],
    ['GET',  '/events/edit/{id}',      [EventController::class, 'editForm']],
    ['POST', '/events/edit/{id}',      [EventController::class, 'editSubmit']],
    ['POST', '/events/delete/{id}',    [EventController::class, 'deleteEvent']],
    ['GET',  '/events/edit/{id}',   [EventController::class, 'editForm']],
    ['POST', '/events/edit/{id}',   [EventController::class, 'editSubmit']],
    ['POST', '/events/delete/{id}', [EventController::class, 'deleteSubmit']],
];
