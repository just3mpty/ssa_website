<?php

declare(strict_types=1);

namespace CapsuleLib\Router;

require dirname(__DIR__, 2) . '/config/routes.php';

/**
 * Routeur minimaliste pour applications PHP sans framework.
 *
 * Associe des chemins d'URL à des méthodes de contrôleur via une table de routage définie dans `config/routes.php`.
 * Supporte les paramètres dynamiques (ex: /user/{id}).
 */
class Router
{
    /**
     * @var array<string, array{controller: class-string, method: string}>
     */
    private array $routes;

    /**
     * @var string URI demandée, sans les slashes de début/fin.
     */
    private string $path;

    /**
     * Instancie le routeur et déclenche l’analyse de l’URL courante.
     */
    public function __construct()
    {
        $this->routes = ROUTES;
        $this->path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $this->handle();
    }

    /**
     * Analyse la route et appelle dynamiquement le bon contrôleur/méthode.
     * Affiche une page 404 si aucun pattern ne correspond.
     *
     * @return void
     */
    private function handle(): void
    {
        foreach ($this->routes as $routePattern => $handler) {
            $routePattern = trim($routePattern, '/');
            $params = [];

            if ($this->match($routePattern, $this->path, $params)) {
                $controller = new $handler['controller'];
                $method = $handler['method'];
                $controller->$method(...$params);
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    /**
     * Vérifie si un chemin demandé correspond à un pattern défini.
     * Extrait les paramètres s’il y en a.
     *
     * @param string               $pattern Pattern à tester (ex: 'user/{id}').
     * @param string               $actual  Chemin réel de la requête (ex: 'user/42').
     * @param array<string, string> &$params Référence pour stocker les paramètres extraits.
     *
     * @return bool True si match trouvé, false sinon.
     */
    private function match(string $pattern, string $actual, array &$params): bool
    {
        $patternParts = explode('/', $pattern);
        $actualParts = explode('/', $actual);

        if (count($patternParts) !== count($actualParts)) {
            return false;
        }

        foreach ($patternParts as $i => $part) {
            if ($this->isParam($part)) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $actualParts[$i];
            } elseif ($part !== $actualParts[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Détermine si une partie d’un chemin est un paramètre dynamique `{...}`.
     *
     * @param string $part Élément du chemin à tester.
     *
     * @return bool True si le fragment est un paramètre, false sinon.
     */
    private function isParam(string $part): bool
    {
        return str_starts_with($part, '{') && str_ends_with($part, '}');
    }
}
