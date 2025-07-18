<?php

declare(strict_types=1);

namespace CapsuleLib\Http\Middleware;

use CapsuleLib\Security\Authenticator;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Authenticator::isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }
}
