<?php

declare(strict_types=1);

use CapsuleLib\Security\Html;

// Échappe une chaîne pour affichage HTML (contre XSS)
if (!function_exists('secure_html')) {
    function secure_html(?string $str): string
    {
        return Html::escape($str);
    }
}

// Échappe une chaîne pour un attribut HTML (value="", href="", etc.)
if (!function_exists('secure_attr')) {
    function secure_attr(?string $str): string
    {
        return Html::escapeAttr($str);
    }
}

// Échappe une URL (évite javascript: ou autres schémas dangereux)
if (!function_exists('secure_url')) {
    function secure_url(?string $url): string
    {
        return Html::escapeUrl($url);
    }
}

// Échappe une chaîne pour injection JS inline (var foo = '...';)
if (!function_exists('secure_js')) {
    function secure_js(?string $str): string
    {
        return Html::escapeJs($str);
    }
}

// Échappe récursivement un array ou un DTO (ex: EventDTO, UserDTO)
if (!function_exists('secure_data')) {
    function secure_data(array|object $data): array
    {
        return Html::escapeArray($data);
    }
}
