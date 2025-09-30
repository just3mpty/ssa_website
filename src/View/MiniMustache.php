<?php

// src/View/MiniMustache.php
declare(strict_types=1);

namespace Capsule\View;

final class MiniMustache
{
    public function __construct(private string $baseDir)
    {
        $real = realpath($baseDir);
        if ($real === false) {
            throw new \RuntimeException("Templates dir not found: {$baseDir}");
        }
        $this->baseDir = $real;
    }

    /** @param array<string,mixed> $data */
    public function render(string $templatePath, array $data = []): string
    {
        $tpl = $this->load($templatePath);

        return $this->compile($tpl, $data);
    }

    /** @param array<string,mixed> $data */
    private function compile(string $tpl, array $data): string
    {
        // Partials: {{> partial/name}}
        $tpl = preg_replace_callback('/\{\{\>\s*([a-zA-Z0-9_\/\-\.]+)\s*\}\}/', function ($m) use ($data) {
            return $this->render($m[1] . '.tpl.php', $data);
        }, $tpl) ?? $tpl;

        // Sections (each): {{#each key}}...{{/each}}
        $tpl = preg_replace_callback(
            '/\{\{\#each\s+([a-zA-Z0-9_.]+)\s*\}\}([\s\S]*?)\{\{\/each\}\}/',
            function ($m) use ($data) {
                $arr = $this->get($data, $m[1]);
                if (!is_iterable($arr)) {
                    return '';
                }
                $chunk = '';
                foreach ($arr as $item) {
                    $chunk .= $this->compile($m[2], $this->with($data, $item));
                }

                return $chunk;
            },
            $tpl
        ) ?? $tpl;

        // Sections booléennes: {{# flag }} ... {{/flag}}
        $tpl = preg_replace_callback(
            '/\{\{\#\s*([a-zA-Z0-9_.]+)\s*\}\}([\s\S]*?)\{\{\/\s*\1\s*\}\}/',
            function ($m) use ($data) {
                $v = $this->get($data, $m[1]);
                // "truthy" si non vide / vrai / non null
                $truthy = false;
                if (is_array($v) || $v instanceof \Countable) {
                    $truthy = (count($v) > 0);
                } else {
                    $truthy = (bool)$v;
                }

                return $truthy ? $this->compile($m[2], $data) : '';
            },
            $tpl
        ) ?? $tpl;

        // Sections inverses: {{^ flag }} ... {{/flag}}
        $tpl = preg_replace_callback(
            '/\{\{\^\s*([a-zA-Z0-9_.]+)\s*\}\}([\s\S]*?)\{\{\/\s*\1\s*\}\}/',
            function ($m) use ($data) {
                $v = $this->get($data, $m[1]);
                $falsy = false;
                if (is_array($v) || $v instanceof \Countable) {
                    $falsy = (count($v) === 0);
                } else {
                    $falsy = !$v;
                }

                return $falsy ? $this->compile($m[2], $data) : '';
            },
            $tpl
        ) ?? $tpl;

        // Raw HTML (triple mustache): {{{ key }}}
        $tpl = preg_replace_callback('/\{\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}\}/', function ($m) use ($data) {
            return (string)($this->get($data, $m[1]) ?? '');
        }, $tpl) ?? $tpl;

        // Escaped variables: {{ key }}
        $tpl = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function ($m) use ($data) {
            $v = $this->get($data, $m[1]);

            return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }, $tpl) ?? $tpl;

        return $tpl;
    }

    private function load(string $relPath): string
    {
        if (str_contains($relPath, "\0") || str_contains($relPath, "\r") || str_contains($relPath, "\n")) {
            throw new \InvalidArgumentException('Invalid template path');
        }
        $abs = $this->baseDir . '/' . ltrim($relPath, '/');

        // D’abord : fichier présent ?
        if (!file_exists($abs)) {
            throw new \InvalidArgumentException("Template not found: {$abs}");
        }

        $real = realpath($abs);
        if ($real === false || !str_starts_with($real, $this->baseDir . DIRECTORY_SEPARATOR)) {
            throw new \InvalidArgumentException('Template outside base dir');
        }

        $s = file_get_contents($real);
        if ($s === false) {
            throw new \RuntimeException("Cannot read template: {$real}");
        }

        return $s;
    }

    /**
 * @return int|(array&array<string,mixed>)|(array<string,mixed>) @param array<string,mixed> $base */
    private function with(array $base, mixed $ctx): array
    {
        // Fusionne contexte courant + item (item > base)
        if (is_array($ctx)) {
            return $ctx + $base;
        }
        if (is_object($ctx)) {
            return get_object_vars($ctx) + $base;
        }

        return ['.' => $ctx] + $base; // accès {{ . }} si valeur scalaire
    }
    /**
     * @param array<int,mixed> $data
     */
    private function get(array $data, string $key): mixed
    {
        $parts = explode('.', $key);
        $cur = $data;
        foreach ($parts as $p) {
            if (is_array($cur) && array_key_exists($p, $cur)) {
                $cur = $cur[$p];
                continue;
            }
            if (is_object($cur) && isset($cur->$p)) {
                $cur = $cur->$p;
                continue;
            }

            return null;
        }

        return $cur;
    }
}
