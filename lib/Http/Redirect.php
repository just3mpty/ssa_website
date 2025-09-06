<?php

declare(strict_types=1);

namespace CapsuleLib\Http;

final class Redirect
{
    public static function to(string $path, int $status = 303): never
    {
        header('Location: ' . $path, true, $status);
        exit;
    }
}
