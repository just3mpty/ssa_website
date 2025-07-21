<?php

declare(strict_types=1);

namespace CapsuleLib\Router;


class Router
{
    private array $routes = []; // ['GET' => [pattern => [controller, method]], ...]
    private $notFoundHandler = null;

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }
    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }
    public function any(string $path, array $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $m) {
            $this->addRoute($m, $path, $handler);
        }
    }

    public function setNotFoundHandler(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $pattern = $this->convertPathToRegex($path);
        $this->routes[$method][$pattern] = $handler;
    }

    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('#\{([a-z]+)\}#i', '(?P<$1>[^/]+)', trim($path, '/'));
        return '#^' . $pattern . '$#';
    }

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
