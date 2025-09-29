<?php

declare(strict_types=1);

namespace Capsule\Routing;

final class NotFound extends \RuntimeException
{
    public function __construct(string $msg = 'Route not found')
    {
        parent::__construct($msg);
    }
}
