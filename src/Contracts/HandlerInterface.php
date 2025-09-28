<?php

declare(strict_types=1);

namespace Capsule\Contracts;

use Capsule\Http\Request;
use Capsule\Http\Response;

interface HandlerInterface
{
    public function handle(Request $req): Response;
}
