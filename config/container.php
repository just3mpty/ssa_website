<?php

declare(strict_types=1);

use App\Controller\AgendaController;
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
    $c = new DIContainer();
    $LENGTH_PASSWORD = 8;

    // --- Core deps ---
    $c->set('pdo', fn () => MariaDBConnection::getInstance());

    $c->set(DebugHeaders::class, fn ($c) => new DebugHeaders(
        res: $c->get(\Capsule\Contracts\ResponseFactoryInterface::class),
        enabled: true // passe à false en prod
    ));

    $c->set(ErrorBoundary::class, fn ($c) => new ErrorBoundary(
        $c->get(ResponseFactoryInterface::class),
        debug: true,
        appName: 'SSA Website'
    ));
    $c->set(
        SecurityHeaders::class,
        fn () => new SecurityHeaders(
            dev: true,   // <-- mets false en prod
            https: false // true si HTTPS prod
        )
    );


    $c->set(ResponseFactoryInterface::class, fn () => new ResponseFactory());
    $c->set(ViewRendererInterface::class, function () {
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
    $c->set(ArticleRepository::class, fn ($c) => new ArticleRepository($c->get('pdo')));
    $c->set(UserRepository::class, fn ($c) => new UserRepository($c->get('pdo')));

    // --- Services ---
    $c->set(
        ArticleService::class,
        fn ($c) => new ArticleService($c->get(ArticleRepository::class))
    );
    $c->set(UserService::class, fn ($c) => new UserService($c->get(UserRepository::class)));
    $c->set('passwords', fn ($c) => new PasswordService(
        $c->get(UserRepository::class),
        $LENGTH_PASSWORD,
        []
    ));

    // --- Navigation ---
    $c->set(SidebarLinksProvider::class, fn () => new SidebarLinksProvider());

    // --- Controllers ---
    $c->set(HelloController::class, fn ($c) => new HelloController(
        $c->get(ResponseFactoryInterface::class)
    ));

    $c->set(HomeController::class, fn ($c) => new HomeController(
        $c->get(\App\Service\ArticleService::class),
        $c->get(ResponseFactoryInterface::class),
        $c->get(ViewRendererInterface::class),
    ));
    $c->set(LoginController::class, fn ($c) => new LoginController(
        $c->get('pdo'),
        $c->get(ResponseFactoryInterface::class),
        $c->get(ViewRendererInterface::class),
    ));

    $c->set(AgendaController::class, fn ($c) => new AgendaController(
        $c->get(ResponseFactoryInterface::class),
        $c->get(ViewRendererInterface::class),
    ));

    $c->set(DashboardController::class, fn ($c) => new DashboardController(
        $c->get(UserService::class),
        $c->get('passwords'),
        $c->get(SidebarLinksProvider::class),
        $c->get(ResponseFactoryInterface::class),
        $c->get(ViewRendererInterface::class),
    ));
    $c->set(UserController::class, fn ($c) => new UserController(
        $c->get(UserService::class),
        $c->get('passwords'),
    ));
    $c->set(ArticlesController::class, fn ($c) => new ArticlesController(
        $c->get(ArticleService::class),
        $c->get(SidebarLinksProvider::class),
        $c->get(ResponseFactoryInterface::class),
        $c->get(ViewRendererInterface::class),
    ));

    $c->set(CalendarController::class, fn ($c) => new CalendarController());

    return $c;
})();
