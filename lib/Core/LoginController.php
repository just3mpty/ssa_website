<?php

declare(strict_types=1);

namespace CapsuleLib\Core;

use CapsuleLib\Core\RenderController;
use CapsuleLib\Security\Authenticator;
use CapsuleLib\Security\CsrfTokenManager;
use App\Lang\TranslationLoader;
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
        echo $this->renderView('admin/login.php', [
            'showHeader' => true,
            'showFooter' => true,
            'title' => 'Connexion',
            'error' => null,
            'str'   => $this->getStrings(),
        ]);
    }

    /**
     * Traite la soumission du formulaire de connexion.
     * Vérifie le token CSRF, tente d'authentifier l'utilisateur.
     * En cas de succès, redirige vers le dashboard.
     * Sinon, réaffiche le formulaire avec un message d'erreur.
     *
     * @return void
     */
    public function loginSubmit(): void
    {
        CsrfTokenManager::requireValidToken();

        $success = Authenticator::login(
            $this->pdo,
            $_POST['username'] ?? '',
            $_POST['password'] ?? ''
        );

        if ($success) {
            header('Location: ../dashboard/home');
            exit;
        }

        echo $this->renderView('admin/login.php', [
            'title' => 'Connexion',
            'showHeader' => true,
            'showFooter' => true,
            'error' => 'Identifiants incorrects.',
            'str'   => $this->getStrings(),
        ]);
    }

    /**
     * Affiche le tableau de bord de l'administration.
     * Vérifie que l'utilisateur est authentifié.
     * Fournit les données utilisateur et permissions au template.
     *
     * @return void
     */
    /**
     * Déconnecte l'utilisateur, détruit la session et redirige vers la page de login.
     *
     * @return void
     */
    public function logout(): void
    {
        Authenticator::logout();
        header('Location: /login');
        exit;
    }
}
