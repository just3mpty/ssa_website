<?php

declare(strict_types=1);

namespace Capsule\View;

use Capsule\Contracts\ViewRendererInterface;
use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Http\Message\Response;

/**
 * Contrôleur de Base
 *
 * Classe abstraite fournissant les méthodes utilitaires communes à tous les contrôleurs.
 * Gère le rendu des vues, les redirections et l'injection des dépendances.
 *
 * @package Capsule\View
 * @abstract
 */
abstract class BaseController
{
    /**
     * Namespace par défaut pour les pages (surchargeable)
     *
     * @var string Ex: 'dashboard' pour les pages du dashboard
     */
    protected string $pageNs = '';

    /**
     * Namespace par défaut pour les composants (surchargeable)
     *
     * @var string Ex: 'dashboard' pour les composants du dashboard
     */
    protected string $componentNs = '';

    /**
     * Constructeur du contrôleur de base
     *
     * @param ResponseFactoryInterface $res Factory pour créer des réponses HTTP
     * @param ViewRendererInterface $view Moteur de rendu des vues
     */
    public function __construct(
        protected ResponseFactoryInterface $res,
        protected ViewRendererInterface $view
    ) {
    }

    /**
     * Rend une template HTML avec layout (méthode legacy)
     *
     * @deprecated Utiliser page() à la place
     * @param string $template Chemin de la template
     * @param array<string,mixed> $data Données à passer à la template
     * @param int $status Code HTTP de la réponse
     * @return Response Réponse HTTP
     */
    protected function html(string $template, array $data = [], int $status = 200): Response
    {
        $out = $this->view->render($template, $data);

        return $this->res->html($out, $status);
    }

    /**
     * Crée une redirection HTTP
     *
     * @param string $location URL de destination
     * @param int $status Code HTTP de redirection (302 par défaut)
     * @return Response Réponse de redirection
     */
    protected function redirect(string $location, int $status = 302): Response
    {
        return $this->res->redirect($location, $status);
    }

    /**
     * Rend un composant sans layout (méthode legacy)
     *
     * @deprecated Utiliser comp() à la place
     * @param string $componentPath Chemin du composant
     * @param array<string,mixed> $data Données à passer au composant
     * @return string HTML du composant
     */
    protected function component(string $componentPath, array $data = []): string
    {
        return $this->view->renderComponent($componentPath, $data);
    }

    /**
     * Rend une page avec layout via noms logiques
     *
     * Utilise les noms logiques pour résoudre les templates avec layout automatique.
     * Ex: 'home' → 'page:home', 'dashboard:home' → 'dashboard:home'
     *
     * @param string $name Nom logique de la page
     * @param array<string,mixed> $data Données à passer à la page
     * @param int $status Code HTTP de la réponse
     * @return Response Réponse HTTP avec la page rendue
     */
    protected function page(string $name, array $data = [], int $status = 200): Response
    {
        // Résolution du nom logique
        $logical = str_contains($name, ':')
            ? $name
            : ($this->pageNs !== '' ? "page:{$this->pageNs}/{$name}" : "page:{$name}");

        $out = $this->view->render($logical, $data);

        return $this->res->html($out, $status);
    }

    /**
     * Rend un composant (fragment) sans layout via noms logiques
     *
     * Utilise les noms logiques pour résoudre les composants sans layout.
     * Ex: 'dashboard/articles' → 'component:dashboard/articles'
     *
     * @param string $name Nom logique du composant
     * @param array<string,mixed> $data Données à passer au composant
     * @return string HTML du composant
     */
    protected function comp(string $name, array $data = []): string
    {
        $logical = str_contains($name, ':')
            ? $name
            : ($this->componentNs !== '' ? "component:{$this->componentNs}/{$name}" : "component:{$name}");

        return $this->view->renderComponent($logical, $data);
    }
}
