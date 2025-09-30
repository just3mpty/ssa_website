<?php

declare(strict_types=1);

namespace App\Controller;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Http\Support\Cookie;
use Capsule\Routing\Attribute\Route;
use Capsule\Routing\Attribute\RoutePrefix;

#[RoutePrefix('/hello')]
final class HelloController
{
    public function __construct(private ResponseFactoryInterface $res)
    {
    }

    #[Route(path: '', methods: ['GET'])]
    public function index(Request $req): Response
    {
        return $this->res->json(['message' => 'Hello World!']);
    }

    #[Route(path: '/text', methods: ['GET'])]
    public function text(): Response
    {
        return $this->res->text("plain text\n");
    }

    #[Route(path: '/html', methods: ['GET'])]
    public function html(): Response
    {
        $body = <<<HTML
        <!doctype html>
        <meta charset="utf-8">
        <title>Hello</title>
        <h1>Hello <em>HTML</em> !</h1>
        HTML;

        return $this->res->html($body);
    }

    #[Route(path: '/redirect', methods: ['GET'])]
    public function redirect(): Response
    {
        return $this->res->redirect('/hello');
    }

    #[Route(path: '/redirect303', methods: ['POST'])]
    public function redirect303(): Response
    {
        return $this->res->redirect('/hello', 303);
    }

    #[Route(path: '/download', methods: ['GET'])]
    public function download(): Response
    {
        $csv = "id,name\n1,Ada\n2,Linus\n";

        return $this->res->download('users.csv', $csv, 'text/csv');
    }

    #[Route(path: '/created', methods: ['GET'])]
    public function created(): Response
    {
        $newId = 42;

        return $this->res->created(
            "/hello/resource/{$newId}",
            ['id' => $newId, 'status' => 'created']
        );
    }

    #[Route(path: '/empty', methods: ['DELETE'])]
    public function empty(): Response
    {
        return $this->res->empty(204);
    }

    #[Route(path: '/stream', methods: ['GET'])]
    public function jsonStream(): Response
    {
        $items = (function () {
            foreach (range(1, 5) as $i) {
                yield ['n' => $i, 'square' => $i * $i];
            }
        })();

        return $this->res->jsonStream($items, fn (array $row) => $row);
    }

    #[Route(path: '/dl-stream', methods: ['GET'])]
    public function downloadStream(): Response
    {
        $content = (function () {
            yield "chunk-1\n";
            yield "chunk-2\n";
            yield "chunk-3\n";
        })();

        return $this->res->downloadStream('chunks.txt', $content, 'text/plain');
    }

    #[Route(path: '/problem', methods: ['GET'])]
    public function problem(): Response
    {
        return $this->res->problem([
            'type' => 'https://example.com/probs/invalid-state',
            'title' => 'Invalid state',
            'status' => 409,
            'detail' => 'Resource is in a conflicting state',
        ], 409);
    }

    #[Route(path: '/cookie', methods: ['GET'])]
    public function cookie(): Response
    {
        $r = $this->res->json(['ok' => true]);
        $cookie = new Cookie(
            name: 'session',
            value: 'abc123',
            maxAge: 3600,
            path: '/',
            httpOnly: true,
            secure: true,
            sameSite: 'Lax'
        );

        return $this->res->withCookie($r, $cookie);
    }

    #[Route(path: '/nocache', methods: ['GET'])]
    public function noCache(): Response
    {
        $r = $this->res->json(['timestamp' => time()]);

        return $this->res->noCache($r);
    }
}
