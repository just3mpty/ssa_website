<?php

declare(strict_types=1);

namespace Capsule\Routing;

use Capsule\Contracts\RouterInterface;
use Capsule\Http\HttpException;
use Capsule\Http\Request;

/**
 * Router exact-match (méthode + chemin).
 *
 * Contrats :
 * - Méthode comparée en uppercase.jj
 * - Fallback : HEAD utilise le handler GET s’il existe.
 * - 404 si aucun handler pour le path.
 * - 405 si path existe mais pas pour la méthode ; renvoie header Allow.
 *
 * @psalm-type Handler = callable(Request)
 */

final class Router implements RouterInterface
{
    /** @var array<string, array<string, callable>> method => [path => handler] */
    private array $routes = [];

    /**
     * @param callable(Request):Response $handler
     */
    public function add(string $method, string $path, callable $handler): void
    {
        $m = strtoupper($method);
        $this->routes[$m][$path] = $handler;
    }

    /**
     * @return callable(Request):Response
     * @throws HttpException 404|405
     */
    public function match(Request $request): callable
    {
        $method = $request->method;
        $path   = $request->path;

        // Route exacte pour la méthode ?
        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path];
        }

        // Fallback HEAD -> GET
        if ($method === 'HEAD' && isset($this->routes['GET'][$path])) {
            return $this->routes['GET'][$path];
        }

        // Path existe-t-il pour au moins une méthode ?
        $allowed = $this->allowedMethodsForPath($path);
        if ($allowed !== []) {
            // RFC : la liste Allow est séparée par ", "
            throw new HttpException(405, 'Method Not Allowed', ['Allow' => [implode(', ', $allowed)]]);
        }

        throw new HttpException(404, 'Not Found');
    }

    /**
     * @return list<string> Liste de méthodes autorisées pour ce path (triées)
     */
    private function allowedMethodsForPath(string $path): array
    {
        $methods = [];
        foreach ($this->routes as $m => $table) {
            if (isset($table[$path])) {
                $methods[] = $m;
            }
        }
        sort($methods);
        return $methods;
    }
}
