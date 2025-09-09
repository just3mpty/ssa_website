<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use App\Service\ArticleService;
use CapsuleLib\Core\RenderController;
use CapsuleLib\Http\RequestUtils;

// TODO: : Mettre les articles dans le home
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

    public function article(string|array $params): void
    {
        $id = is_array($params) ? (int)($params['id'] ?? 0) : (int)$params;
        if ($id <= 0) {
            http_response_code(400);
            echo 'Bad Request';
            return;
        }

        $dto = $this->articleService->getById($id);
        if (!$dto) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        echo $this->renderView('pages/articleDetails.php', $this->base([
            'article' => $dto,
        ], /* withArticles */ false));
    }

    public function contactMail(): void 
    {
    RequestUtils::ensurePostOrRedirect('/contact');

    if (isset($_POST['message']) && isset($_POST['email']) && isset($_POST['name'])) {
            $to = 'noah.moaligou@gmail.com'; // Adresse qui recevra le mail
            $subject = 'Message depuis le formulaire de contact ' . $_POST['name'];
            $message = $_POST['message'];
            $headers = 'From: ' . $_POST['email'] . "\r\n" .
                    'Reply-To: ' . $_POST['email'] . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

            $sent = mail($to, $subject, $message, $headers);

            if ($sent) {
                echo "Votre message a bien été envoyé.";
            } else {
                $error = error_get_last();
                $details = isset($error['message']) ? $error['message'] : 'Erreur inconnue.';
                echo "Une erreur est survenue lors de l'envoi :<br><pre>{$details}</pre>";
            }
        } else {
            echo "Veuillez remplir tous les champs du formulaire.";
        }
    }
}
