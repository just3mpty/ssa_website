<?php

namespace CapsuleLib\Template;

class TemplateCompiler
{
    private string $sourceDir;
    private string $cacheDir;

    public function __construct(string $sourceDir, string $cacheDir)
    {
        $this->sourceDir = rtrim($sourceDir, '/');
        $this->cacheDir = rtrim($cacheDir, '/');
    }

    public function render(string $view, array $context = []): void
    {
        $compiledPath = $this->getCompiledPath($view);
        $sourcePath = "{$this->sourceDir}/{$view}.view";

        if (!file_exists($compiledPath) || $this->needsRecompile($sourcePath, $compiledPath)) {
            $compiled = $this->compile(file_get_contents($sourcePath));
            file_put_contents($compiledPath, $compiled);
        }

        extract($context, EXTR_SKIP);
        require $compiledPath;
    }

    private function getCompiledPath(string $view): string
    {
        return "{$this->cacheDir}/{$view}.compiled.php";
    }

    private function needsRecompile(string $src, string $compiled): bool
    {
        return !file_exists($compiled) || filemtime($src) > filemtime($compiled);
    }

    private function compile(string $tpl): string
    {
        // Expressions {{ var }} => echo htmlspecialchars(...)
        $tpl = preg_replace('/{{\s*(.+?)\s*}}/', '<?php echo htmlspecialchars($1); ?>', $tpl);

        // Expressions {{ raw(var) }} => echo $var (non-échappé)
        $tpl = preg_replace('/{{\s*raw\((.+?)\)\s*}}/', '<?php echo $1; ?>', $tpl);

        // Components <Component attr="val" />
        $tpl = preg_replace_callback(
            '/<([A-Z][a-zA-Z0-9]*)\s*(.*?)\s*\/>/',
            fn($m) => $this->compileComponent($m[1], $m[2]),
            $tpl
        );

        // @if, @elseif, @else, @endif
        $tpl = preg_replace('/@if\s*\((.*?)\)/', '<?php if ($1): ?>', $tpl);
        $tpl = preg_replace('/@elseif\s*\((.*?)\)/', '<?php elseif ($1): ?>', $tpl);
        $tpl = preg_replace('/@else/', '<?php else: ?>', $tpl);
        $tpl = preg_replace('/@endif/', '<?php endif; ?>', $tpl);

        // @foreach and @endforeach
        $tpl = preg_replace('/@foreach\s*\((.*?)\)/', '<?php foreach ($1): ?>', $tpl);
        $tpl = preg_replace('/@endforeach/', '<?php endforeach; ?>', $tpl);

        return $tpl;
    }

    private function compileComponent(string $name, string $attrStr): string
    {
        preg_match_all('/(\w+)="([^"]*)"/', $attrStr, $matches, PREG_SET_ORDER);
        $params = array_map(fn($m) => "'{$m[1]}' => {$m[2]}", $matches);
        $args = implode(', ', $params);
        return "<?php echo \$this->component('$name', [$args]); ?>";
    }
}
