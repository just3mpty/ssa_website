<?php

declare(strict_types=1);

namespace Capsule\Contracts;

interface ContainerLike
{
    public function get(string $id): mixed;
}
