## 1. Sécurité & Échappement automatique

On ne fait **pas** de moteur de template à proprement parler (pas Twig/Blade), mais on a mis en place des helpers PHP qui permettent d’échapper proprement les données côté serveur.

### Fonctions d’échappement à utiliser absolument

* `secure_html($str)` → Pour échapper toute donnée affichée dans du contenu HTML (évite XSS)
* `secure_attr($str)` → Pour échapper les attributs HTML (`href`, `alt`, `value`, etc.)
* `secure_url($url)` → Pour sécuriser les URLs (bloque les schémas dangereux comme `javascript:`)
* `secure_js($str)` → Pour sécuriser des chaînes injectées en JS inline (rarement utilisé)
* `secure_data($arrOrObj)` → Pour échapper récursivement un tableau ou un DTO complet

> **Exemple classique :**
>
> ```php
> <h3><?= secure_html($article->titre) ?></h3>
> <a href="<?= secure_url($article->url) ?>" title="<?= secure_attr($article->titre) ?>">Lien</a>
> ```

---

## 2. Internationalisation (i18n)

On gère les traductions avec une classe statique `Translate` et un loader dynamique.

### Comment ça marche ?

* Les traductions sont stockées sous `src/Lang/locales/{lang}/{page}.php`
* Chaque page PHP charge ses traductions via `$str = TranslationLoader::load(defaultLang: 'fr', page: 'home')`
* On passe `$str` dans les vues, c’est un tableau clé/valeur avec toutes les chaînes traduites

### Dans les templates

> Exemple d’utilisation simple :
>
> ```php
> <h1><?= secure_html($str['hero_title']) ?></h1>
> <p><?= secure_html($str['hero_slogan']) ?></p>
> ```

### Passage de la langue

* Langue détectée dans la session ou via `?lang=fr` / `?lang=br`
* Sélecteur dans le header gère ça automatiquement

---

## 3. Architecture des vues & templates

* Backend génère des vues PHP classiques avec `renderView($path, $params)`
* Les variables accessibles sont passées dans `$params`
* On utilise des composants réutilisables via `$this->renderComponent('component.php')` (exemple : footer, header, actualites)
* Toujours utiliser les fonctions d’échappement ci-dessus sur les données dynamiques

---

## 4. DTO & données

* Les données métier sont passées en **DTOs** immutables (ex: `ArticleDTO`), avec des propriétés typées
* Dans les vues, tu peux accéder aux propriétés comme `$article->titre`, `$article->date_article` etc.
* Pour les tableaux simples, pareil : utiliser les helpers d’échappement

---

## 5. Quelques bonnes pratiques

* Toujours **échappe** toute donnée dynamique sortie dans le HTML ou JS avec les helpers (`secure_html()`, `secure_attr()`, etc.)
* Tu peux demander au backend d’ajouter des chaînes traduites dans `$str` pour le texte statique dynamique (ex : titres, labels)
* Le backend passe souvent `$str` + des objets DTO dans les vues
* Les formulaires sont protégés via un token CSRF automatique (`CsrfTokenManager::insertInput()`)
* La session est gérée côté PHP, pas besoin de s’en préoccuper côté frontend

---

## 6. Pour aller plus loin côté frontend

* Tu peux enrichir les composants PHP avec des scripts JS / CSS sans problème (cf dossier `/assets/`)
* Si tu veux un comportement plus dynamique (filtrage, affichage), fais-le en JS classique ou via framework de ton choix
* Backend prépare les données, toi tu les rends interactives côté client

---

## 7. Structure des routes (pour info)

Voici un aperçu des routes dispo et leurs méthodes (GET, POST) :

| Route                 | Méthode  | Contrôleur / Action                        | Usage                    |
| --------------------- | -------- | ------------------------------------------ | ------------------------ |
| `/`                   | GET      | HomeController::home                       | Page d’accueil           |
| `/projet`             | GET      | HomeController::projet                     | Page projet              |
| `/galerie`            | GET      | HomeController::galerie                    | Page galerie             |
| `/wiki`               | GET      | HomeController::wiki                       | Wiki                     |
| `/login`              | GET/POST | AdminController::loginForm / loginSubmit   | Authentification         |
| `/dashboard`          | GET      | AdminController::dashboard                 | Tableau de bord admin    |
| `/logout`             | GET      | AdminController::logout                    | Déconnexion              |
| `/articles`             | GET      | ArticleController::listArticles                | Liste des événements     |
| `/articles/create`      | GET/POST | ArticleController::createForm / createSubmit | Création d’événement     |
| `/articles/edit/{id}`   | GET/POST | ArticleController::editForm / editSubmit     | Modification d’événement |
| `/articles/delete/{id}` | POST     | ArticleController::deleteSubmit              | Suppression d’événement  |

---

## 8. En résumé

Tu as côté backend :

* Une API PHP simple basée sur des vues PHP avec templates PHP classiques
* Un système d’échappement manuel mais bien centralisé et sûr
* Une gestion de l’i18n via tableau `$str` avec clé/valeur
* Des DTO immuables pour les données structurées
* Un router HTTP basique qui fait matcher routes et contrôleurs

Tu peux te concentrer côté frontend sur :

* Rendre proprement les vues avec `<?= secure_html(...) ?>`
* Ajouter JS/CSS pour l’interactivité
* Utiliser `$str` pour les chaînes traduites dynamiques
* Protéger les formulaires via le token CSRF injecté automatiquement

---

Si tu as besoin de quoi que ce soit (exemples, explications, snippets), hésite pas à demander.
