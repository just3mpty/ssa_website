<?php

declare(strict_types=1);

namespace CapsuleLib\Http;

final class RequestUtils
{
    /** Retourne la méthode HTTP (GET, POST, etc.) */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /** Vérifie si la requête est en POST */
    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /**
     * Récupère une valeur depuis $_POST avec filtre optionnel.
     *
     * @param string     $key     Nom de la clé POST
     * @param int|null   $filter  Constante FILTER_* de PHP
     * @param mixed|null $default Valeur par défaut si absente
     *
     * @return string|int|float|bool|null
     */
    public static function post(string $key, ?int $filter = null, mixed $default = null): string|int|float|bool|null
    {
        return filter_input(INPUT_POST, $key, $filter ?? FILTER_UNSAFE_RAW) ?? $default;
    }

    /**
     * Normalise un paramètre provenant du routeur (string|int|array) en int.
     *
     * @param string|int|array<string,int|string> $p
     * @return int
     */
    public static function intFromParam(string|int|array $p): int
    {
        return (int)(\is_array($p) ? ($p['id'] ?? 0) : $p);
    }
}
