<?php

declare(strict_types=1);

namespace App\Controller;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Factory\ResponseFactory as Res;
use Capsule\Http\Support\Cookie;
use Capsule\Routing\Attribute\Route;
use Capsule\Routing\Attribute\RoutePrefix;

#[RoutePrefix('/hello')]
final class HelloController
{
    // GET /hello  → JSON simple
    #[Route(path: '', methods: ['GET'])]
    public function index(Request $req): Response
    {
        return Res::json(['message' => 'Hello World!']);
    }

    // GET /hello/text  → texte brut
    #[Route(path: '/text', methods: ['GET'])]
    public function text(): Response
    {
        return Res::text("plain text\n");
    }

    // GET /hello/html  → HTML
    #[Route(path: '/html', methods: ['GET'])]
    public function html(): Response
    {
        $body = <<<HTML
        <!doctype html>
        <meta charset="utf-8">
        <title>Hello</title>
        <h1>Hello <em>HTML</em> !</h1>
        HTML;

        return Res::html($body);
    }

    // GET /hello/redirect  → redirection 302
    #[Route(path: '/redirect', methods: ['GET'])]
    public function redirect(): Response
    {
        return Res::redirect('/hello'); // 302 par défaut
    }

    // POST /hello/redirect303  → redirection 303 (POST→GET)
    #[Route(path: '/redirect303', methods: ['POST'])]
    public function redirect303(): Response
    {
        return Res::redirect('/hello', 303);
    }

    // GET /hello/download  → téléchargement (corps string)
    #[Route(path: '/download', methods: ['GET'])]
    public function download(): Response
    {
        $csv = "id,name\n1,Ada\n2,Linus\n";

        return Res::download('users.csv', $csv, 'text/csv');
    }

    // GET /hello/created  → 201 Created + Location (+ option body JSON)
    #[Route(path: '/created', methods: ['GET'])]
    public function created(): Response
    {
        $newId = 42;

        return Res::created(
            "/hello/resource/{$newId}",
            ['id' => $newId, 'status' => 'created']
        );
    }

    // DELETE /hello/empty  → 204 No Content
    #[Route(path: '/empty', methods: ['DELETE'])]
    public function empty(): Response
    {
        return Res::empty(204);
    }

    // GET /hello/stream  → NDJSON streaming (une ligne JSON par record)
    #[Route(path: '/stream', methods: ['GET'])]
    public function jsonStream(): Response
    {
        $items = (function () {
            // Simule une source paresseuse
            foreach (range(1, 5) as $i) {
                yield ['n' => $i, 'square' => $i * $i];
            }
        })();

        return Res::jsonStream($items, fn (array $row) => $row);
    }

    // GET /hello/dl-stream  → téléchargement streamé
    #[Route(path: '/dl-stream', methods: ['GET'])]
    public function downloadStream(): Response
    {
        $content = (function () {
            yield "chunk-1\n";
            yield "chunk-2\n";
            yield "chunk-3\n";
        })();

        return Res::downloadStream('chunks.txt', $content, 'text/plain');
    }

    // GET /hello/problem  → application/problem+json (RFC 7807)
    #[Route(path: '/problem', methods: ['GET'])]
    public function problem(): Response
    {
        return Res::problem([
            'type' => 'https://example.com/probs/invalid-state',
            'title' => 'Invalid state',
            'status' => 409,
            'detail' => 'Resource is in a conflicting state',
        ], 409);
    }

    // GET /hello/cookie  → Set-Cookie sécurisé + JSON
    #[Route(path: '/cookie', methods: ['GET'])]
    public function cookie(): Response
    {
        $r = Res::json(['ok' => true]);
        $cookie = new Cookie(
            name: 'session',
            value: 'abc123',
            maxAge: 3600,
            path: '/',
            httpOnly: true,
            secure: true,
            sameSite: 'Lax'
        );

        return Res::withCookie($r, $cookie);
    }

    // GET /hello/nocache  → en-têtes no-store/no-cache + JSON
    #[Route(path: '/nocache', methods: ['GET'])]
    public function noCache(): Response
    {
        $r = Res::json(['timestamp' => time()]);

        return Res::noCache($r);
    }
}
