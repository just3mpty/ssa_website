<?php

declare(strict_types=1);

use App\Controller\HomeController;
use App\Controller\DashboardController;
use App\Controller\LoginController;
use App\Controller\ArticlesController;
use CapsuleLib\Core\DIContainer;
use CapsuleLib\Routing\Router;
use CapsuleLib\Middleware\MiddlewareAuth;

/** @return callable(Router, DIContainer): void */
return static function (Router $router, DIContainer $c): void {
    $hc = $c->get(HomeController::class);
    $dc = $c->get(DashboardController::class);
    $aa = $c->get(ArticlesController::class);
    $lc = $c->get(LoginController::class);

    $router->get('/health', function () {
        header('Content-Type: text/plain');
        echo "OK";
    });

    // Public
    $router->get('/',        [$hc, 'home'],   [], name: 'home');
    $router->get('/projet',  [$hc, 'projet']);
    $router->get('/galerie', [$hc, 'galerie']);

    // Auth
    $router->get('/login',  [$lc, 'loginForm']);
    $router->post('/login', [$lc, 'loginSubmit']);
    $router->get('/logout', [$lc, 'logout']);

    // Dashboard (auth requis)
    $router->group('/dashboard', [MiddlewareAuth::auth()], function (Router $r) use ($dc, $aa) {
        $r->get('/home',    [$dc, 'home'],   name: 'dash.home');
        $r->get('/account', [$dc, 'account'], name: 'dash.account');
        $r->post('/account/password', [$dc, 'accountPassword']);

        // Users (admin)
        $r->group('/', [MiddlewareAuth::role('admin')], function (Router $r2) use ($dc) {
            $r2->get('/users',            [$dc, 'users'],        name: 'dash.users');
            $r2->post('/users/create',    [$dc, 'usersCreate']);
            $r2->post('/users/delete',    [$dc, 'usersDelete']);
        });

        // Articles admin (admin)
        $r->group('/', [MiddlewareAuth::role('admin')], function (Router $r3) use ($aa) {
            $r3->get('/articles', [$aa, 'index'], name: 'dash.articles.index'); // /dashboard/articles/
            $r3->get('/articles/create',         [$aa, 'createForm'],  name: 'dash.articles.create');
            $r3->post('/articles/create',        [$aa, 'createSubmit']);
            $r3->get('/articles/edit/{id:\d+}',  [$aa, 'editForm'],    name: 'dash.articles.edit');
            $r3->post('/articles/edit/{id:\d+}', [$aa, 'editSubmit']);
            $r3->post('/articles/delete/{id:\d+}', [$aa, 'deleteSubmit'], name: 'dash.articles.delete');
        });
    });
};
