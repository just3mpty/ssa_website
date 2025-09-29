<?php

declare(strict_types=1);

namespace Capsule\Http\Factory;

use Capsule\Http\Message\Response;
use Capsule\Http\Support\Cookie;
use JsonSerializable;

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
            $json = json_encode(['error' => 'Invalid JSON payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $status = 500;
        }

        return (new Response($status, (string)$json))
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
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

    /**
     * Redirect helper — valide le Location et borne les statuts.
     * (RFC 9110 : Location relative ou absolue autorisée)
     */
    public static function redirect(string $location, int $status = 302): Response
    {
        if (!in_array($status, [301, 302, 303, 307, 308], true)) {
            throw new \InvalidArgumentException('Redirect status must be one of 301,302,303,307,308');
        }
        self::assertHeaderValueSafe($location, 'Location');

        // Petit body utile si client non-navigateur
        $body = "Redirecting to: {$location}\n";

        return (new Response($status, $body))
            ->withHeader('Location', $location)
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withHeader('Cache-Control', 'no-store');
    }

    public static function download(
        string $filename,
        string $content,
        string $contentType = 'application/octet-stream'
    ): Response {
        [$dispValue, $dispUtf8] = self::buildContentDispositionValues($filename);

        return (new Response(200, $content))
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Content-Disposition', "attachment; {$dispValue}; {$dispUtf8}")
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('Cache-Control', 'no-store');
    }

    /**
     * @param mixed[]|JsonSerializable|null $body
     */
    public static function created(string $location, array|\JsonSerializable|null $body = null): Response
    {
        self::assertHeaderValueSafe($location, 'Location');
        $res = new Response(201, $body === null ? '' : (string)json_encode(
            $body,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));
        $res = $res->withHeader('Location', $location);
        if ($body !== null) {
            $res = $res->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        return $res;
    }

    public static function empty(int $status = 204): Response
    {
        if ($status < 100 || $status > 599) {
            throw new \InvalidArgumentException('Invalid status');
        }

        return new Response($status, '');
    }


    /** @param iterable<mixed> $items */
    public static function jsonStream(iterable $items, ?callable $toRow = null): Response
    {
        $toRow ??= static fn ($x) => $x;
        $iter = (function () use ($items, $toRow) {
            foreach ($items as $it) {
                yield json_encode($toRow($it), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
            }
        })();

        return (new Response(200, $iter))
            ->withHeader('Content-Type', 'application/x-ndjson; charset=utf-8');
    }


    /** @param iterable<string> $content */
    public static function downloadStream(
        string $filename,
        iterable $content,
        string $contentType = 'application/octet-stream'
    ): Response {
        [$dispValue, $dispUtf8] = self::buildContentDispositionValues($filename);

        return (new Response(200, $content))
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Content-Disposition', "attachment; {$dispValue}; {$dispUtf8}")
            ->withHeader('Cache-Control', 'no-store');
    }


    /** @param array<string,mixed> $problem */
    public static function problem(array $problem, int $status = 400): Response
    {
        $json = json_encode($problem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return (new Response($status, (string)$json))
            ->withHeader('Content-Type', 'application/problem+json; charset=utf-8');
    }

    public static function withCookie(Response $r, Cookie $cookie): Response
    {
        $header = $cookie->toHeader();
        self::assertHeaderValueSafe($header, 'Set-Cookie');

        return $r->withAddedHeader('Set-Cookie', $header);
    }

    public static function noCache(Response $r): Response
    {
        return $r->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
                 ->withAddedHeader('Pragma', 'no-cache')
                 ->withHeader('Expires', '0');
    }

    // ---------- Helpers sécu en-têtes ----------

    private static function assertHeaderValueSafe(string $v, string $name): void
    {
        if (str_contains($v, "\r") || str_contains($v, "\n")) {
            throw new \InvalidArgumentException("Invalid header value for {$name} (CR/LF not allowed)");
        }
        // Borne défensive (facultatif)
        if (strlen($v) > 8192) {
            throw new \InvalidArgumentException("Header value for {$name} too long");
        }
    }

    /**
     * Construit "filename" (RFC 6266) + "filename*" UTF-8 (RFC 5987).
     * - Échappe les guillemets et backslashes dans filename=
     * - Encode percent-encoding dans filename*=
     * @return array{string,string} [ filename=..., filename*=... ]
     */
    private static function buildContentDispositionValues(string $filename): array
    {
        // filename= → ASCII-safe + quotes escaped
        $safe = str_replace(['\\','"'], ['\\\\','\\"'], $filename);
        // filename* → UTF-8 percent-encoded
        $utf8 = rawurlencode($filename);

        $dispValue = 'filename="' . $safe . '"';
        $dispUtf8 = "filename*=UTF-8''" . $utf8;

        self::assertHeaderValueSafe($safe, 'Content-Disposition filename');
        self::assertHeaderValueSafe($utf8, 'Content-Disposition filename*');

        return [$dispValue, $dispUtf8];
    }
}
