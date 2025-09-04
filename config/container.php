<?php

declare(strict_types=1);

use CapsuleLib\Core\DIContainer;
use CapsuleLib\Database\MariaDBConnection;
use CapsuleLib\Repository\UserRepository;
use CapsuleLib\Service\UserService;
use CapsuleLib\Core\LoginController;

use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use App\Navigation\SidebarLinksProvider;
use App\Controller\HomeController;
use App\Controller\ArticlesController;
use App\Controller\DashboardController;


//HACK : (Optionnel) Interfaces lib → impls src 

// use CapsuleLib\Security\AuthenticatorInterface;
// use App\Security\SessionAuthenticator;

return (function (): DIContainer {
    $container = new DIContainer();
    $LENGTH_PASSWORD = 8;


    // --- Core deps ---
    $container->set('pdo', fn() => MariaDBConnection::getInstance());

    // --- Repositories ---
    $container->set(ArticleRepository::class, fn($container) => new ArticleRepository($container->get('pdo')));
    $container->set(UserRepository::class,    fn($container) => new UserRepository($container->get('pdo')));

    // --- Services ---
    $container->set(ArticleService::class, fn($container) => new ArticleService($container->get(ArticleRepository::class)));
    $container->set(UserService::class,    fn($container) => new UserService($container->get(UserRepository::class)));
    $container->set('passwords',           fn($container) => new \CapsuleLib\Service\PasswordService(
        $container->get(UserRepository::class),
        $LENGTH_PASSWORD,
        []
    ));

    // --- Navigation ---
    $container->set(SidebarLinksProvider::class, fn($container) => new SidebarLinksProvider());

    // --- Controllers ---
    $container->set(HomeController::class,      fn($container) => new HomeController($container->get(ArticleService::class)));
    $container->set(LoginController::class,       fn($container) => new LoginController($container->get('pdo')));
    $container->set(DashboardController::class,   fn($container) => new DashboardController(
        $container->get(UserService::class),
        $container->get(ArticleService::class),
        $container->get('passwords'),
        $container->get(SidebarLinksProvider::class),
    ));
    $container->set(ArticlesController::class, fn($container) => new ArticlesController(
        $container->get(ArticleService::class),
        $container->get(SidebarLinksProvider::class),
    ));

    // NOTE: 
    // (Optionnel) Binders d’interfaces → impls projet
    // $container->set(AuthenticatorInterface::class, fn($container) => new SessionAuthenticator(...));

    return $container;
})();
