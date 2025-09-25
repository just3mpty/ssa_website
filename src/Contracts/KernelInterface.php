<?php

declare(strict_types=1);

namespace Capsule\Contracts;

use Capsule\Http\Request;
use Capsule\Http\Response;

/**
 * Contrat du kernel HTTP.
 *
 * Invariants d’implémentation :
 * - handle() retourne toujours une Response (ne laisse pas fuiter d’exception).
 */
interface KernelInterface
{
    public function handle(Request $request): Response;
}
