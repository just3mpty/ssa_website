<?php

declare(strict_types=1);

namespace CapsuleLib\Http;

final class RequestUtils
{
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }
    public static function post(string $key, ?int $filter = null, $default = null)
    {
        return filter_input(INPUT_POST, $key, $filter ?? FILTER_UNSAFE_RAW) ?? $default;
    }
    public static function intFromParam(string|int|array $p): int
    {
        return (int)(is_array($p) ? ($p['id'] ?? 0) : $p);
    }
}
