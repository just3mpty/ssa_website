<?php
// config/container.php
declare(strict_types=1);

use CapsuleLib\Core\DIContainer;
use CapsuleLib\Database\MariaDBConnection;

use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use App\Controller\HomeController;
use App\Controller\DashboardController;
use App\Controller\ArticleController;
use App\Controller\ArticlesAdminController;

use CapsuleLib\Repository\UserRepository;
use CapsuleLib\Service\UserService;
use CapsuleLib\Core\LoginController;

// (Optionnel) Interfaces lib → impls src si tu en as
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
        $container->get(UserRepository::class), // ou un adapter si tu as créé une interface PasswordStore
        $LENGTH_PASSWORD,
        []
    ));

    // --- Controllers ---
    $container->set(HomeController::class,        fn($container) => new HomeController($container->get(ArticleService::class)));
    $container->set(ArticleController::class,     fn($container) => new ArticleController($container->get(ArticleService::class)));
    $container->set(LoginController::class,       fn($container) => new LoginController($container->get('pdo')));
    $container->set(DashboardController::class,   fn($container) => new DashboardController(
        $container->get(UserService::class),
        $container->get(ArticleService::class),
        $container->get('passwords'),
    ));
    $container->set(ArticlesAdminController::class, fn($container) => new ArticlesAdminController($container->get(ArticleService::class)));

    // (Optionnel) Binders d’interfaces → impls projet
    // $container->set(AuthenticatorInterface::class, fn($container) => new SessionAuthenticator(...));

    return $container;
})();
