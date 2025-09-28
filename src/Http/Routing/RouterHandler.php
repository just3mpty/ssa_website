<?php

namespace Capsule\Http\Routing;

use Capsule\Contracts\HandlerInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

final class RouterHandler implements HandlerInterface
{
    /** @var array<string, callable(Request): Response> */
    private array $routes = [];
    /**
     * @param callable(): mixed $controller
     */
    public function add(string $method, string $path, callable $controller): void
    {
        $key = strtoupper($method) . ' ' . $path;
        $this->routes[$key] = $controller;
    }

    public function handle(Request $req): Response
    {
        $key = $req->method . ' ' . $req->path;
        $ctl = $this->routes[$key] ?? null;

        if (!$ctl) {
            //throw new NotFound('Route not found');
        } // 404

        return $ctl($req);
    }
}
