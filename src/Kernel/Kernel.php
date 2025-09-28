<?php

declare(strict_types=1);

namespace Capsule\Kernel;

use Capsule\Contracts\ControllerResolverInterface;
use Capsule\Contracts\KernelInterface;
use Capsule\Contracts\RouterInterface;
use Capsule\Http\HttpException;
use Capsule\Http\Request;
use Capsule\Http\Response;

final class Kernel implements KernelInterface
{
    /** @param list<MiddlewareInterface> $middlewares */
    public function __construct(
        private RouterInterface $router,
        private array $middlewares = [],
        private ?ControllerResolverInterface $resolver = null
    ) {
    }

    public function handle(Request $request): Response
    {
        $dispatcher = $this->makeDispatcher();

        try {
            return $dispatcher($request);
        } catch (HttpException $e) {
            // Mappe l'exception HTTP en réponse
            $r = new Response($e->status, $this->safeBody($e->getMessage()));
            foreach ($e->headers as $n => $vals) {
                foreach ($vals as $v) {
                    $r = $r->withAddedHeader($n, $v);
                }
            }
            // Défauts sûrs
            if (!$r->hasHeader('Content-Type')) {
                $r = $r->withHeader('Content-Type', 'text/plain; charset=utf-8')
                       ->withHeader('X-Content-Type-Options', 'nosniff');
            }
            return $r->withStatus($e->status);
        } catch (\Throwable $e) {
            // 500 opaque par défaut (pas de leak d’info en prod)
            return (new Response(500, 'Internal Server Error'))
                ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                ->withHeader('X-Content-Type-Options', 'nosniff');
        }
    }

    private function makeDispatcher(): callable
    {
        // Terminal : route -> handler -> Response
        $last = function (Request $req): Response {
            $desc = $this->router->match($req);
            $callable = is_callable($desc)
                ? $desc
                : ($this->resolver?->resolve($desc)
                    ?? throw new \LogicException('Unresolvable controller'));
            $resp = $callable($req);
            if (!$resp instanceof Response) {
                throw new \LogicException('Controller must return a Response');
            }
            return $resp;
        };

        // Enroule les middlewares en LIFO
        $next = $last;
        for ($i = \count($this->middlewares) - 1; $i >= 0; $i--) {
            $mw = $this->middlewares[$i];
            $prevNext = $next;
            $next = static fn (Request $r): Response => $mw->process($r, $prevNext);
        }
        return $next;
    }

    private function safeBody(string $message): string
    {
        // Corps simple texte (pas d’HTML) pour éviter l’injection.
        return $message !== '' ? $message : 'Error';
    }
}
