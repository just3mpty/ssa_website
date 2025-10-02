# Framework CapsulePHP - Référence PHPDoc Complète

## Vue d'ensemble

Cette documentation couvre l'ensemble des composants du framework CapsulePHP avec leurs descriptions PHPDoc complètes.

## Architecture du Framework

### Kernel HTTP

**Fichier**: `src/Kernel/KernelHttp.php`

- **Rôle**: Orchestrateur principal du pipeline middleware LIFO
- **Pattern**: Middleware pipeline avec exécution LIFO (Last-In-First-Out)
- **Responsabilités**:
  - Orchestration des middlewares
  - Gestion des exceptions non attrapées
  - Résolution des handlers de requêtes

### Container DI

**Fichier**: `src/Infrastructure/Container/DIContainer.php`

- **Rôle**: Conteneur d'injection de dépendances singleton
- **Pattern**: Singleton avec factories pour injection de dépendances
- **Fonctionnalités**:
  - Résolution automatique des dépendances
  - Support des factories
  - Gestion du cycle de vie des services

### Système de Routage

**Fichier**: `src/Routing/Discovery/RouteScanner.php`

- **Rôle**: Scanner automatique des routes par attributs
- **Pattern**: Découverte automatique via réflexion
- **Fonctionnalités**:
  - Scan des contrôleurs avec attributs `#[Route]` et `#[RoutePrefix]`
  - Compilation des routes pour performance
  - Support des méthodes HTTP multiples

## Interfaces Principales

### HandlerInterface

**Fichier**: `src/Contracts/HandlerInterface.php`

- **Rôle**: Interface de gestionnaire de requêtes HTTP
- **Méthode**: `handle(Request $request): Response`
- **Utilisation**: Point d'entrée du pipeline middleware

### MiddlewareInterface

**Fichier**: `src/Contracts/MiddlewareInterface.php`

- **Rôle**: Interface pour les middlewares single-pass
- **Contrats**:
  - Appeler `$next($request)` au plus une fois
  - Retourner toujours une Response
- **Pattern**: Single-pass middleware

### ContainerLike

**Fichier**: `src/Contracts/ContainerLike.php`

- **Rôle**: Interface pour les conteneurs d'injection de dépendances
- **Méthode**: `get(string $id): mixed`
- **Utilisation**: Résolution de services par identifiant

### ResponseFactoryInterface

**Fichier**: `src/Contracts/ResponseFactoryInterface.php`

- **Rôle**: Factory pour créer différents types de réponses HTTP
- **Types supportés**:
  - JSON, HTML, texte brut
  - Redirections
  - Téléchargements de fichiers
  - Réponses de problème (RFC 7807)

## Middlewares

### ErrorBoundary

**Fichier**: `src/Http/Middleware/ErrorBoundary.php`

- **Rôle**: Gestion centralisée des erreurs
- **Fonctionnalités**:
  - Capture toutes les exceptions non attrapées
  - Transforme en réponses JSON standardisées
  - Ajoute un identifiant unique par requête
  - Mode débogage avec informations détaillées

### SecurityHeaders

**Fichier**: `src/Http/Middleware/SecurityHeaders.php`

- **Rôle**: Ajout d'en-têtes de sécurité HTTP
- **En-têtes ajoutés**:
  - Content-Security-Policy (CSP)
  - X-Content-Type-Options: nosniff
  - Referrer-Policy: no-referrer
  - X-Frame-Options: DENY
  - Strict-Transport-Security (en production HTTPS)

### AuthRequiredMiddleware

**Fichier**: `src/Http/Middleware/AuthRequiredMiddleware.php`

- **Rôle**: Vérification d'authentification par rôle
- **Fonctionnalités**:
  - Protection par préfixe de routes
  - Liste blanche d'URLs exemptées
  - Redirection vers page de connexion
  - Vérification de rôle utilisateur

### DebugHeaders

**Fichier**: `src/Http/Middleware/DebugHeaders.php`

- **Rôle**: Endpoint de débogage des en-têtes
- **Utilisation**: Inspection des en-têtes de requête vus par PHP
- **Endpoint**: `/__debug/headers` (activé en mode développement)

## Composants HTTP

### Request

**Fichier**: `src/Http/Message/Request.php`

- **Rôle**: Représentation immuable d'une requête HTTP
- **Fonctionnalités**:
  - Normalisation et sécurisation des données
  - Construction à partir des superglobales PHP
  - Protection contre l'injection d'en-têtes
  - Décodage sécurisé du chemin

### Response

**Fichier**: `src/Http/Message/Response.php`

- **Rôle**: Représentation immuable d'une réponse HTTP
- **Implémente**: `ResponseInterface`
- **Fonctionnalités**:
  - Méthodes immuables pour modification
  - Gestion des en-têtes insensible à la casse
  - Support des corps de réponse en flux

### ResponseFactory

**Fichier**: `src/Http/Factory/ResponseFactory.php`

- **Rôle**: Factory pour créer des réponses HTTP standardisées
- **Implémente**: `ResponseFactoryInterface`
- **Types de réponses**:
  - JSON avec encodage UTF-8
  - HTML avec protection XSS
  - Redirections sécurisées
  - Téléchargements de fichiers
  - Réponses de problème RFC 7807

## Composants de Sécurité

### CsrfTokenManager

**Fichier**: `src/Security/CsrfTokenManager.php`

- **Rôle**: Protection contre les attaques CSRF
- **Fonctionnalités**:
  - Génération de tokens cryptographiquement sécurisés
  - Comparaison à temps constant avec `hash_equals()`
  - Insertion automatique dans les formulaires
  - Validation obligatoire pour les requêtes POST

### Authenticator

**Fichier**: `src/Security/Authenticator.php`

- **Rôle**: Service d'authentification sécurisé
- **Pratiques de sécurité**:
  - Régénération d'ID de session
  - Cookies HTTP-only
  - Mode strict de session
  - SameSite Strict
  - Vérification de mot de passe avec `password_verify()`

### CurrentUserProvider

**Fichier**: `src/Security/CurrentUserProvider.php`

- **Rôle**: Fournisseur d'informations utilisateur
- **Fonctionnalités**:
  - Accès aux données de session utilisateur
  - Vérification d'authentification
  - Vérification de rôle administrateur
  - Utilisation dans les templates

## Système de Vues

### BaseController

**Fichier**: `src/View/BaseController.php`

- **Rôle**: Contrôleur de base avec méthodes utilitaires
- **Fonctionnalités**:
  - Rendu de templates avec données
  - Redirections HTTP
  - Gestion des erreurs
  - Méthodes utilitaires pour contrôleurs enfants

### MiniMustache

**Fichier**: `src/View/MiniMustache.php`

- **Rôle**: Moteur de templates Mustache-like
- **Syntaxe**: Support partiel de la syntaxe Mustache
- **Fonctionnalités**:
  - Échappement HTML automatique
  - Partials dynamiques
  - Layout principal avec header/footer

### FilesystemTemplateLocator

**Fichier**: `src/View/FilesystemTemplateLocator.php`

- **Rôle**: Localisateur de templates sur le système de fichiers
- **Patterns supportés**:
  - `page:dashboard/home` → templates/dashboard/home.tpl.php
  - `component:dashboard/user` → templates/components/dashboard/user.tpl.php
  - `partial:header` → templates/partials/header.tpl.php

## Composants de Support

### HeaderBag

**Fichier**: `src/Http/Message/HeaderBag.php`

- **Rôle**: Gestionnaire d'en-têtes HTTP
- **Fonctionnalités**:
  - Stockage case-insensitive
  - Support des valeurs multiples
  - Méthodes pour ajouter/supprimer/enlever

### Cookie

**Fichier**: `src/Http/Support/Cookie.php`

- **Rôle**: Représentation d'un cookie HTTP
- **Fonctionnalités**:
  - Construction sécurisée
  - Génération d'en-tête Set-Cookie
  - Support des attributs de sécurité

## Patterns Architecturaux

### Middleware Pipeline LIFO

- **Pattern**: Last-In-First-Out middleware execution
- **Avantages**:
  - Exécution prévisible
  - Gestion cohérente des erreurs
  - Composition flexible

### Dependency Injection

- **Pattern**: Injection de dépendances via conteneur
- **Avantages**:
  - Découplage des composants
  - Testabilité améliorée
  - Configuration centralisée

### Immutable Objects

- **Pattern**: Objets immuables pour Request/Response
- **Avantages**:
  - Thread-safety
  - Comportement prévisible
  - Facilité de débogage

## Bonnes Pratiques Documentées

### Sécurité

- **Validation CSRF**: Obligatoire sur toutes les actions POST
- **Échappement HTML**: Automatique dans les templates
- **Sessions sécurisées**: HTTP-only, SameSite Strict, régénération d'ID
- **En-têtes de sécurité**: CSP, HSTS, X-Frame-Options

### Performance

- **Routage compilé**: Routes pré-compilées pour performance
- **Middleware LIFO**: Exécution optimisée
- **Streaming**: Support des réponses en flux

### Maintenabilité

- **Types stricts**: PHP 8.2+ avec `declare(strict_types=1)`
- **Documentation complète**: PHPDoc détaillé
- **Tests unitaires**: Couverture avec PHPUnit
- **Standards de code**: PSR-12 avec règles personnalisées

## Guide d'Utilisation

### Création d'un Contrôleur

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Routing\Attribute\Route;
use Capsule\View\BaseController;

final class HomeController extends BaseController
{
    #[Route(path: '/', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('page:home', [
            'title' => 'Accueil',
            'message' => 'Bienvenue sur notre site'
        ]);
    }
}
```

### Ajout d'un Middleware Personnalisé

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Capsule\Contracts\HandlerInterface;
use Capsule\Contracts\MiddlewareInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

final class CustomMiddleware implements MiddlewareInterface
{
    public function process(Request $request, HandlerInterface $next): Response
    {
        // Logique avant le handler
        $response = $next->handle($request);
        // Logique après le handler
        
        return $response;
    }
}
```

Cette documentation couvre l'ensemble du framework CapsulePHP avec des descriptions détaillées en français pour faciliter la compréhension et l'utilisation par l'équipe de développement.