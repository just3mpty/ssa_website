<?php

declare(strict_types=1);

namespace Capsule\Http;

final class Request
{
    /** @param array<string,string> $headers */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $headers,
        public readonly array $cookies,
        public readonly array $server,
        public readonly string $scheme = 'http',
        public readonly ?string $host = null,
        public readonly ?int $port = null,
        public readonly ?string $rawBody = null,
    ) {}

    public static function fromGlobals(): self
    {
        $srv = $_SERVER;

        // 1) Méthode (uppercase, fallback GET)
        $method = strtoupper($srv['REQUEST_METHOD'] ?? 'GET');
        if (!preg_match('/^[A-Z]+$/', $method)) {
            $method = 'GET';
        }

        // 2) Path normalisé (sans query, root par défaut, sécurité)
        $uri  = (string)($srv['REQUEST_URI'] ?? '/');
        $path = strtok($uri, '?') ?: '/';
        $path = $path === '' ? '/' : $path;
        // bloque null bytes et directory traversal naïf
        if (str_contains($path, "\0")) {
            $path = '/';
        }
        // decode percent-encoding SANS transformer '+' en espace (RFC3986)
        $path = rawurldecode($path);
        // optionnel: compacter les doubles slashes (sauf préfixe)
        $path = preg_replace('#//+#', '/', $path) ?? $path;

        // 3) En-têtes (sans getallheaders)
        $headers = [];
        foreach ($srv as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
                $headers[$name] = self::sanitizeHeaderValue((string)$v);
            } elseif (in_array($k, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $k))));
                $headers[$name] = self::sanitizeHeaderValue((string)$v);
            }
        }

        // 4) Scheme/host/port (ne PAS faire confiance aux X-Forwarded-* par défaut)
        $https  = ($srv['HTTPS'] ?? '') && strtolower((string)$srv['HTTPS']) !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host   = $headers['Host'] ?? ($srv['SERVER_NAME'] ?? null) ?? null;
        $port   = isset($srv['SERVER_PORT']) ? (int)$srv['SERVER_PORT'] : null;

        // 5) Raw body (utile pour JSON)
        $rawBody = file_get_contents('php://input') ?: null;

        return new self(
            method: $method,
            path: $path,
            query: $_GET,
            headers: $headers,
            cookies: $_COOKIE,
            server: $srv,
            scheme: $scheme,
            host: $host,
            port: $port,
            rawBody: $rawBody,
        );
    }

    private static function sanitizeHeaderValue(string $v): string
    {
        // Empêche l'injection d'en-têtes
        return str_replace(["\r", "\n"], '', $v);
    }
}
