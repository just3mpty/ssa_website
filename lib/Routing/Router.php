<?php

declare(strict_types=1);

namespace CapsuleLib\Routing;

/**
 * Router minimaliste basé sur les méthodes HTTP et les patterns d’URL.
 *
 * Permet de définir des routes avec leurs handlers (méthode et contrôleur),
 * de gérer les routes GET, POST, ou "any" (toutes méthodes principales),
 * et de dispatcher la requête courante vers le handler adéquat.
 *
 * Le pattern d’URL peut contenir des paramètres nommés {param}, accessibles dans la méthode appelée.
 *
 * Exemple :
 *  $router->get('/user/{id}', [$userController, 'show']);
 *  $router->dispatch();
 */
class Router
{
    /** @var array<string, array<string, array{0: object, 1: string}>> Liste des routes par méthode HTTP */
    private array $routes = [];

    /** @var callable|null Handler pour les routes non trouvées (404) */
    private $notFoundHandler = null;

    /**
     * Définit une route HTTP GET.
     *
     * @param string $path Pattern de l’URL (ex: /user/{id})
     * @param array{0: object, 1: string} $handler Couple [contrôleur, méthode]
     * @return void
     */
    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Définit une route HTTP POST.
     *
     * @param string $path Pattern de l’URL
     * @param array{0: object, 1: string} $handler Couple [contrôleur, méthode]
     * @return void
     */
    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Définit une route accessible par toutes les méthodes HTTP principales.
     *
     * @param string $path Pattern de l’URL
     * @param array{0: object, 1: string} $handler Couple [contrôleur, méthode]
     * @return void
     */
    public function any(string $path, array $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $this->addRoute($method, $path, $handler);
        }
    }

    /**
     * Définit un handler custom pour les requêtes non matchées (404).
     *
     * @param callable $handler Fonction à exécuter en cas de 404
     * @return void
     */
    public function setNotFoundHandler(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    /**
     * Ajoute une route pour une méthode HTTP donnée.
     *
     * Convertit le pattern d’URL en regex et stocke le handler.
     *
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $path Pattern de l’URL
     * @param array{0: object, 1: string} $handler Couple [contrôleur, méthode]
     * @return void
     */
    private function addRoute(string $method, string $path, array $handler): void
    {
        $pattern = $this->convertPathToRegex($path);
        $this->routes[$method][$pattern] = $handler;
    }

    /**
     * Convertit un pattern d’URL avec paramètres en expression régulière.
     *
     * Exemple : /user/{id} → /^user\/(?P<id>[^/]+)$/
     *
     * @param string $path Pattern d’URL
     * @return string Expression régulière délimitée pour preg_match
     */
    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('#\{([a-z]+)\}#i', '(?P<$1>[^/]+)', trim($path, '/'));
        return '#^' . $pattern . '$#';
    }

    /**
     * Traite la requête HTTP courante, cherche une route correspondante et exécute son handler.
     *
     * Passe les paramètres extraits de l’URL à la méthode cible.
     * Si aucune route ne correspond, exécute le handler 404 ou affiche un message.
     *
     * @return void
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');

        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);

                [$controller, $methodName] = $handler;
                $controller->$methodName(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        if (isset($this->notFoundHandler)) {
            call_user_func($this->notFoundHandler);
        } else {
            echo "404 Not Found";
        }
    }
}
