<?php

namespace Capsule\View;

use Capsule\Contracts\ViewRendererInterface;
use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Http\Message\Response;

abstract class BaseController
{
    public function __construct(
        protected ResponseFactoryInterface $res,
        protected ViewRendererInterface $view
    ) {
    }


    /**
     * @param array<int,mixed> $data Rend une vue HTML avec layout (200 OK par défaut). */
    protected function html(string $template, array $data = [], int $status = 200): Response
    {
        $out = $this->view->render($template, $data);

        return $this->res->html($out, $status);
    }

    /** Redirection HTTP (ne fait pas d’I/O, pas de die). */
    protected function redirect(string $location, int $status = 302): Response
    {
        return $this->res->redirect($location, $status);
    }

    /**
    * @param array<int,mixed> $data Rend un composant (fragment) – utile si tu veux l’inclure côté serveur. */
    protected function component(string $componentPath, array $data = []): string
    {
        return $this->view->renderComponent($componentPath, $data);
    }
}
