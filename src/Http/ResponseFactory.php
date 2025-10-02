<?php

declare(strict_types=1);

namespace Capsule\Http;

final class ResponseFactory
{
    /**
     * @param array<string,mixed>|\JsonSerializable $data
     */
    public static function json(array|\JsonSerializable $data, int $status = 200): Response
    {
        try {
            $json = json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            $json = json_encode(['error' => 'Invalid JSON payload']);
            $status = 500;
        }

        return (new Response($status, (string)$json))
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withHeader('X-Content-Type-Options', 'nosniff');
    }

    public static function text(string $body, int $status = 200): Response
    {
        return (new Response($status, $body))
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withHeader('X-Content-Type-Options', 'nosniff');
    }

    public static function html(string $body, int $status = 200): Response
    {
        return (new Response($status, $body))
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withHeader('X-Content-Type-Options', 'nosniff');
    }

    public static function redirect(string $location, int $status = 302): Response
    {
        if ($status < 300 || $status > 399) {
            throw new \InvalidArgumentException('Redirect status must be 3xx');
        }
        return (new Response($status))
            ->withHeader('Location', $location)
            ->withHeader('Cache-Control', 'no-store');
    }

    public static function download(
        string $filename,
        string $content,
        string $contentType = 'application/octet-stream'
    ): Response {
        return (new Response(200, $content))
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"')
            ->withHeader('X-Content-Type-Options', 'nosniff');
    }

    public static function withCookie(Response $r, Cookie $cookie): Response
    {
        return $r->withAddedHeader('Set-Cookie', $cookie->toHeader());
    }

    public static function noCache(Response $r): Response
    {
        return $r->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
                 ->withAddedHeader('Pragma', 'no-cache')
                 ->withHeader('Expires', '0');
    }
}
