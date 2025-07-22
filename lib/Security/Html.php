<?php

declare(strict_types=1);

namespace CapsuleLib\Security;

final class Html
{
    /**
     * Echappe une chaîne pour un affichage HTML (contre XSS).
     */
    public static function escape(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Echappe une chaîne pour un attribut HTML.
     */
    public static function escapeAttr(?string $str): string
    {
        // Pour la majorité des cas, escape simple suffit.
        // Si besoin d’interdire des caractères spécifiques : personnaliser ici.
        return self::escape($str);
    }

    /**
     * Echappe une URL pour un attribut HTML (href, src).
     * Filtre les schémas dangereux (ex: javascript:).
     */
    public static function escapeUrl(?string $url): string
    {
        $url = trim($url ?? '');
        // Si l’URL n’est pas un schéma autorisé, on renvoie chaîne vide
        if (!preg_match('#^(https?|ftp|mailto):#i', $url)) {
            return '';
        }
        return self::escape($url);
    }

    /**
     * Echappe une chaîne pour un contexte JavaScript inline.
     */
    public static function escapeJs(?string $str): string
    {
        return str_replace(
            ["\\",  "'",   "\"",  "\r", "\n", "</"],
            ["\\\\", "\\'", "\\\"", "\\r", "\\n", "<\\/"],
            $str ?? ''
        );
    }

    /**
     * Echappe récursivement tous les éléments string d’un array ou objet.
     * Pratique pour préparer un tableau destiné à une API, output HTML, etc.
     */
    public static function escapeArray(array|object $data): array
    {
        $escape = fn($v) => is_string($v) ? self::escape($v) : $v;

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = (is_array($v) || is_object($v))
                    ? self::escapeArray($v)
                    : $escape($v);
            }
            return $data;
        }
        if (is_object($data)) {
            $result = [];
            foreach (get_object_vars($data) as $k => $v) {
                $result[$k] = (is_array($v) || is_object($v))
                    ? self::escapeArray($v)
                    : $escape($v);
            }
            return $result;
        }
        return [];
    }
}
