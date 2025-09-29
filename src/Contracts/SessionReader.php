<?php

declare(strict_types=1);

namespace Capsule\Contracts;

interface SessionReader
{
    public function get(string $key, mixed $default = null): mixed;
    public function has(string $key): bool;
}
