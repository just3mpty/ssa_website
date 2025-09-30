<?php

declare(strict_types=1);

use App\Controller\HelloController;
use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Contracts\ViewRendererInterface;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Middleware\DebugHeaders;
use Capsule\Http\Middleware\ErrorBoundary;
use Capsule\Http\Middleware\SecurityHeaders;
use Capsule\Infrastructure\Container\DIContainer;
use Capsule\Infrastructure\Database\MariaDBConnection;
use Capsule\Domain\Repository\UserRepository;
use Capsule\Domain\Service\UserService;
use Capsule\Domain\Service\PasswordService;
use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use App\Navigation\SidebarLinksProvider;
use App\Controller\HomeController;
use App\Controller\LoginController;
use App\Controller\ArticlesController;
use App\Controller\DashboardController;
use App\Controller\CalendarController;
use App\Controller\UserController;

return (function (): DIContainer {
    $container = new DIContainer();
    $LENGTH_PASSWORD = 8;

    // --- Core deps ---
    $container->set('pdo', fn () => MariaDBConnection::getInstance());

    $container->set(DebugHeaders::class, fn ($c) => new DebugHeaders(
        res: $c->get(\Capsule\Contracts\ResponseFactoryInterface::class),
        enabled: true // passe à false en prod
    ));

    $container->set(ErrorBoundary::class, fn ($c) => new ErrorBoundary(
        $c->get(ResponseFactoryInterface::class),
        debug: true,
        appName: 'SSA Website'
    ));
    $container->set(
        SecurityHeaders::class,
        fn () => new SecurityHeaders(
            dev: true,   // <-- mets false en prod
            https: false // true si HTTPS prod
        )
    );


    $container->set(ResponseFactoryInterface::class, fn () => new ResponseFactory());
    $container->set(ViewRendererInterface::class, function () {
        $templatesDir = realpath(dirname(__DIR__) . '/templates');
        if ($templatesDir === false) {
            throw new \RuntimeException('Templates directory not found');
        }

        $engine = new \Capsule\View\MiniMustache($templatesDir);

        return new class ($engine) implements ViewRendererInterface {
            public function __construct(private \Capsule\View\MiniMustache $m)
            {
            }

            public function render(string $templatePath, array $data = []): string
            {
                $content = $this->m->render($templatePath, $data);

                return $this->m->render('layout.tpl.php', $data + ['content' => $content]);
            }

            public function renderComponent(string $componentPath, array $data = []): string
            {
                return $this->m->render('components/' . $componentPath . '.tpl.php', $data);
            }
        };
    });

    // --- Repositories ---
    $container->set(ArticleRepository::class, fn ($container) => new ArticleRepository($container->get('pdo')));
    $container->set(UserRepository::class, fn ($container) => new UserRepository($container->get('pdo')));

    // --- Services ---
    $container->set(
        ArticleService::class,
        fn ($container) => new ArticleService($container->get(ArticleRepository::class))
    );
    $container->set(UserService::class, fn ($container) => new UserService($container->get(UserRepository::class)));
    $container->set('passwords', fn ($container) => new PasswordService(
        $container->get(UserRepository::class),
        $LENGTH_PASSWORD,
        []
    ));

    // --- Navigation ---
    $container->set(SidebarLinksProvider::class, fn () => new SidebarLinksProvider());

    // --- Controllers ---
    $container->set(HelloController::class, fn ($c) => new HelloController(
        $c->get(ResponseFactoryInterface::class)
    ));

    $container->set(HomeController::class, fn ($c) => new HomeController(
        $c->get(\App\Service\ArticleService::class),
        $c->get(ResponseFactoryInterface::class),
        $c->get(ViewRendererInterface::class),
    ));
    $container->set(LoginController::class, fn ($container) => new LoginController($container->get('pdo')));
    $container->set(DashboardController::class, fn ($container) => new DashboardController(
        $container->get(UserService::class),
        $container->get('passwords'),
        $container->get(SidebarLinksProvider::class),
    ));
    $container->set(UserController::class, fn ($container) => new UserController(
        $container->get(UserService::class),
        $container->get('passwords'),
    ));
    $container->set(ArticlesController::class, fn ($container) => new ArticlesController(
        $container->get(ArticleService::class),
        $container->get(SidebarLinksProvider::class),
    ));
    $container->set(CalendarController::class, fn ($container) => new CalendarController());

    return $container;
})();
