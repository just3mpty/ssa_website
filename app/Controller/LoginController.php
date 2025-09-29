<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use Capsule\Security\Authenticator;
use Capsule\Security\CsrfTokenManager;
use Capsule\View\RenderController;
use Capsule\Http\Support\RequestUtils;
use Capsule\Http\Support\Redirect;
use Capsule\Http\Support\FlashBag;
use Capsule\Http\Support\FormState;
use PDO;

/**
 * Contrôleur pour la gestion de l'administration (authentification, tableau de bord).
 *
 * Gère le formulaire de connexion, la soumission du login,
 * l'affichage du dashboard et la déconnexion.
 *
 * Applique la vérification CSRF et la gestion des sessions.
 */
class LoginController extends RenderController
{
    /**
     * Instance PDO pour les opérations liées à la base de données.
     */
    private PDO $pdo;

    /**
     * Constructeur.
     *
     * @param PDO $pdo Instance PDO pour accès base de données.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Charge les chaînes de traduction pour la page courante.
     *
     * @return array<string, string> Tableau associatif des traductions.
     */
    private function getStrings(): array
    {
        return TranslationLoader::load(defaultLang: 'fr');
    }

    /**
     * Affiche le formulaire de connexion.
     *
     * @return void
     */
    public function loginForm(): void
    {
        $errors = FormState::consumeErrors();
        $prefill = FormState::consumeData();
        $payload = [
            'showHeader' => true,
            'showFooter' => true,
            'title' => 'Connexion',
            'error' => $errors['_global'] ?? null,
            'errors' => $errors,
            'prefill' => $prefill,
            'str' => $this->getStrings(),
        ];

        echo $this->renderView('admin/login.php', $payload);
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function loginSubmit(): void
    {
        RequestUtils::ensurePostOrRedirect('/login');
        CsrfTokenManager::requireValidToken();

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            FormState::set(['_global' => 'Champs requis manquants.'], ['username' => $username]);
            FlashBag::add('error', 'Le formulaire contient des erreurs.');
            Redirect::to('/login');
        }

        $success = Authenticator::login(
            $this->pdo,
            $username,
            $password
        );

        if ($success) {
            Redirect::to('/dashboard/account', 302);
        }

        // PRG en cas d'échec d’authentification
        FormState::set(['_global' => 'Identifiants incorrects.'], ['username' => $username]);
        FlashBag::add('error', 'Identifiants incorrects.');
        Redirect::to('/login');
    }

    public function logout(): void
    {
        Authenticator::logout();
        Redirect::to('/login');
    }
}
