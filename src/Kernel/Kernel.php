<?php

declare(strict_types=1);

namespace Capsule\Kernel;

use Capsule\Contracts\HandlerInterface;
use Capsule\Contracts\MiddlewareInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

/**
 * Kernel V2 : compose une pile de middlewares autour d'un HandlerInterface final.
 * Invariants :
 * - L'ordre logique des middlewares est celui fourni (M1, M2, ..., Mn).
 * - Un middleware peut court-circuiter en retournant sa propre Response.
 * - Aucune I/O ici (pas d’emit, pas de header()).
 */
final class Kernel implements HandlerInterface
{
    private HandlerInterface $pipeline;

    /** @param list<Middleware> $middlewares */
    public function __construct(array $middlewares, private HandlerInterface $last)
    {
        // On enrobe le handler final en remontant la pile (LIFO d'assemblage)
        $h = $last;
        for ($i = count($middlewares) - 1; $i >= 0; $i--) {
            $m = $middlewares[$i];
            $h = new class ($m, $h) implements HandlerInterface {
                public function __construct(private MiddlewareInterface $m, private HandlerInterface $next)
                {
                }
                public function handle(Request $r): Response
                {
                    return $this->m->process($r, $this->next);
                }
            };
        }
        $this->pipeline = $h;
    }

    public function handle(Request $req): Response
    {
        return $this->pipeline->handle($req);
    }
}
