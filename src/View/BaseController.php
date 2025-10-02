<?php

namespace Capsule\View;

use Capsule\Contracts\ViewRendererInterface;
use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Http\Message\Response;

abstract class BaseController
{
    /** Namespaces implicites (surchargeables dans chaque contrôleur) */
    protected string $pageNs = '';        // ex: 'dashboard'
    protected string $componentNs = '';   // ex: 'dashboard'

    public function __construct(
        protected ResponseFactoryInterface $res,
        protected ViewRendererInterface $view
    ) {
    }

    /**
 * @param array<int,mixed> $data @deprecated: préfère page() */
    protected function html(string $template, array $data = [], int $status = 200): Response
    {
        $out = $this->view->render($template, $data);

        return $this->res->html($out, $status);
    }

    protected function redirect(string $location, int $status = 302): Response
    {
        return $this->res->redirect($location, $status);
    }

    /**
 * @param array<int,mixed> $data @deprecated: préfère comp() */
    protected function component(string $componentPath, array $data = []): string
    {
        return $this->view->renderComponent($componentPath, $data);
    }

    /**
 * @param array<int,mixed> $data Page avec layout via noms logiques (idempotent). */
    protected function page(string $name, array $data = [], int $status = 200): Response
    {
        // Si déjà préfixé (ex: 'dashboard:home'), ne rien ajouter
        $logical = str_contains($name, ':')
            ? $name
            : ($this->pageNs !== '' ? "page:{$this->pageNs}/{$name}" : "page:{$name}");

        $out = $this->view->render($logical, $data);

        return $this->res->html($out, $status);
    }

    /**
 * @param array<int,mixed> $data Composant (fragment) sans layout via noms logiques (idempotent). */
    protected function comp(string $name, array $data = []): string
    {
        $logical = str_contains($name, ':')
            ? $name
            : ($this->componentNs !== '' ? "component:{$this->componentNs}/{$name}" : "component:{$name}");

        return $this->view->renderComponent($logical, $data);
    }
}
