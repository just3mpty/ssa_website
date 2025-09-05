<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use App\Service\ArticleService;
use CapsuleLib\Core\RenderController;

final class HomeController extends RenderController
{
    private ?array $strings = null;

    public function __construct(private ArticleService $articleService) {}

    /* ---------- Helpers ---------- */

    private function strings(): array
    {
        return $this->strings ??= TranslationLoader::load(defaultLang: 'fr');
    }

    /**
     * Base payload commun aux pages publiques.
     * @param array $extra Variables spécifiques à la vue
     * @param bool  $withArticles Injecte les articles à venir si true
     */
    private function base(array $extra = [], bool $withArticles = true): array
    {
        $base = [
            'showHeader' => true,
            'showFooter' => true,
            'str'        => $this->strings(),
        ];

        if ($withArticles) {
            $base['articles'] = $this->articleService->getUpcoming();
        }

        return array_replace($base, $extra);
    }

    /* ---------- Pages ---------- */

    public function home(): void
    {
        echo $this->renderView('pages/home.php', $this->base());
    }

    public function projet(): void
    {
        echo $this->renderView('pages/projet.php', $this->base());
    }

    public function galerie(): void
    {
        echo $this->renderView('pages/galerie.php', $this->base());
    }

    public function articleDetails(): void
    {
        $id = (int)($_GET['id'] ?? 2);
        $article = $this->articleService->getById($id);

        echo $this->renderView('pages/articleDetails.php', $this->base([
            'article' => $article
        ], false));
    }
}
