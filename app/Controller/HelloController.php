<?php

declare(strict_types=1);

namespace App\Controller;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Factory\ResponseFactory as Res;
use Capsule\Routing\Attribute\Route;
use Capsule\Routing\Attribute\RoutePrefix;

#[RoutePrefix('/hello')]
final class HelloController
{
    #[Route(path: '', methods: ['GET'])]
    public function index(Request $req): Response
    {
        return Res::json(['message' => 'Hello World!']);
    }
}
