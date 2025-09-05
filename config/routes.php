<?php

declare(strict_types=1);

use App\Controller\HomeController;
use App\Controller\DashboardController;
use App\Controller\ArticlesController;
use CapsuleLib\Core\DIContainer;

/**
 * Retourne un tableau de routes [METHOD, PATH, HANDLER].
 * On injecte le container pour construire les handlers.
 *
 * @return array<int, array{0:string,1:string,2:array{0:object,1:string}}>
 */
return static function (DIContainer $c): array {
    $hc = $c->get(HomeController::class);
    $dc = $c->get(DashboardController::class);
    $aa = $c->get(ArticlesController::class);
    $lc = $c->get(\CapsuleLib\Core\LoginController::class);

    return [
        // Public
        ['GET',  '/',        [$hc, 'home']],
        ['GET',  '/projet',  [$hc, 'projet']],
        ['GET',  '/galerie', [$hc, 'galerie']],
        ['GET',  '/wiki',    [$hc, 'wiki']],
        ['POST', '/home/generate_ics',   [$aa, 'generateICS']],
        ['GET',  '/article/{id}', [$hc, 'articleDetails']],

        // Auth
        ['GET',  '/login',  [$lc, 'loginForm']],
        ['POST', '/login',  [$lc, 'loginSubmit']],
        ['GET',  '/logout', [$lc, 'logout']],

        // Dashboard (profil, users)
        ['GET',  '/dashboard/home',             [$dc, 'home']],
        ['GET',  '/dashboard/account',          [$dc, 'account']],
        ['POST', '/dashboard/account/password', [$dc, 'accountPassword']],
        ['GET',  '/dashboard/users',            [$dc, 'users']],
        ['POST', '/dashboard/users/create',     [$dc, 'usersCreate']],
        ['POST', '/dashboard/users/delete',     [$dc, 'usersDelete']],

        // Dashboard articles (admin) —  WARN: doublon /dashboard/articles
        ['GET',  '/dashboard/articles',               [$aa, 'index']],
        ['GET',  '/dashboard/articles/create',        [$aa, 'createForm']],
        ['POST', '/dashboard/articles/create',        [$aa, 'createSubmit']],
        ['GET',  '/dashboard/articles/edit/{id}',     [$aa, 'editForm']],
        ['POST', '/dashboard/articles/edit/{id}',     [$aa, 'editSubmit']],
        ['POST', '/dashboard/articles/delete/{id}',   [$aa, 'deleteSubmit']],
        
    ];
};
