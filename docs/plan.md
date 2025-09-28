# V2 — Kernel + Response (simple) + Router (MVP)

### Portée

* **Contrats** `Handler`, `Middleware`.
* **Kernel** (composition en oignon).
* **Response** **DTO pur** (string uniquement pour cette étape).
* **RouterHandler** minimal (méthode + chemin exacts).
* **Front Controller** d’exemple (sans émission SAPI finale ; utilisé en tests uniquement).

### Tâches

1. **Contrats**

* `Capsule\Http\Handler::handle(Request): Response`
* `Capsule\Http\Middleware::process(Request, Handler): Response`
  **Invariants** : pas d’état global caché ; un middleware appelle `$next` ou retourne une `Response`.

2. **Kernel**

* Construction du pipeline via classes anonymes (wrapper `Handler`).
* Ordre des middlewares conservé (FIFO logique, LIFO d’assemblage).
  **Tests** :

  * appelle tous les middlewares dans l’ordre ;
  * court-circuit si un middleware retourne une `Response`.

3. **Response (V2)**

* Champs : `status:int`, `headers:HeaderBag`, `body:string`.
* Méthodes : `withHeader`, `withAddedHeader`, `withoutHeader`, `withStatus`, `withBody`, `get*`.
  **Invariants** : `100 ≤ status ≤ 599`.
  **NB** : pas de `send()` ici (I/O en V3).

4. **RouterHandler (MVP)**

* Table `{METHOD PATH} -> callable(Request): Response`.
* Match exact (pas de params dynamiques).
* 404 si introuvable (exception `NotFound`).
  **Tests** : 200 sur route connue ; 404 sinon.

5. **Exemples contrôleurs (dev only)**

* `GET /health` → `Response::text("ok")` ou `Response::json(['ok'=>true])` (helpers facultatifs).

### Non-objectifs V2

* Pas d’émetteur SAPI, pas de streaming, pas de headers sécu globaux.

### Acceptation (V2)

* 100% des **tests unitaires** verts pour Kernel/Router/Response.
* **Couverture** cible (mini) : Kernel/Router ≥ 90%, Response ≥ 80%.
* **Complexité** : O(k) middlewares par requête (k = taille de la pile).

---

## V3 — Emitter + Streaming + Middlewares Sécu/Erreurs

### Portée

* **SapiEmitter** (unique point d’I/O HTTP).
* **Response** évolue → `body: string|iterable<string>`.
* **SecurityHeadersMiddleware** (politiques globales minimales).
* **ErrorBoundaryMiddleware** (404/500).
* **(Optionnel)** BodyParser (JSON + limite taille), AccessLog minimal.
* **Routes de démonstration** en **streaming** (NDJSON/CSV).

### Tâches

1. **Response (V3)**

* `withBody(string|iterable<string>)`.
* **Invariants** :

  * Si `body` est iterable ⇒ ne pas auto-poser `Content-Length`.
  * Jamais `Content-Length` et `Transfer-Encoding` à la fois.

2. **SapiEmitter**

* Status via `http_response_code`.
* Émission headers (validation **anti-CRLF** noms/valeurs).
* `Content-Length` auto **uniquement** si : pas d’OB, pas de `zlib.output_compression`, pas de `Transfer-Encoding`, `body` string.
* Émission **chunkée** si `iterable`.
  **Tests** :

  * string → `Content-Length` correct quand autorisé ;
  * iterable → pas de `Content-Length`, envoi par chunks ;
  * header invalide → exception.

3. **SecurityHeadersMiddleware**

* Par défaut :

  * `X-Content-Type-Options: nosniff`
  * `Referrer-Policy: no-referrer`
  * `X-Frame-Options: DENY`
  * `Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'`
    **Invariants** : middleware **idempotent** (n’écrase pas un header déjà présent).

4. **ErrorBoundaryMiddleware**

* Map `NotFound` → 404 JSON.
* `Throwable` → 500 JSON (message détaillé si `debug=true`).
  **Tests** : chemins heureux et erreurs.

5. **Streaming demo**

* `GET /users/export` → NDJSON via générateur.
* Débit stable, O(1) mémoire.
  **Bench court** : 1M lignes en local (valeurs à documenter).

6. **(Optionnel) BodyParser & AccessLog**

* **BodyParser** : JSON strict, limite `maxBytes` (413 si dépassement).
* **AccessLog** : méthode, chemin, statut, durée, taille approx ; **jamais** le corps complet.

### Acceptation (V3)

* **E2E** : front controller exemple (`index.php`) qui : `Request::fromGlobals()` → `Kernel->handle()` → `SapiEmitter->emit()`.
* **SLO initiaux** :

  * p95 `/health` ≤ X ms (local), erreurs ≤ 0.1%.
  * Export NDJSON ≥ Y MiB/s local, mémoire plate (observée).
* **Sécurité** : headers globaux présents ; validation headers côté émetteur.

---

## Risques & parades

* **Confusion rôles** (`Response` vs `Emitter`) → *Parade* : pas d’I/O dans `Response`.
* **Perte de perf** si `Content-Length` mal géré → *Parade* : conditions strictes + tests.
* **Bogue mémoire** sur gros exports → *Parade* : `iterable<string>` + tests de non-matérialisation.
* **Régression sécu** via headers mal formés → *Parade* : validation anti-CRLF obligatoire.


## Prochaine action

* **Lancer V2** : PR avec `Handler`, `Middleware`, `Kernel`, `Response (string)`, `RouterHandler` + tests.

## Vision (bref)

* **V2 (maintenant)** : poser le **Kernel**, un **Response** simple, et **démarrer le Router**. Pas d’I/O SAPI ici.
* **V3 (plus tard)** : ajouter **SapiEmitter**, passer `Response` en **pipeline-friendly** (streaming), brancher **middlewares** sécu/erreurs, et valider **E2E**.

---

```c
                  ┌──────────────────────────┐
        Client →  │   HTTP Request (GET /..) │
                  └──────────────┬───────────┘
                                 │
                                 ▼
                           [ Server ]
                    builds Request::fromGlobals()
                                 │
                                 ▼
                             [ Kernel ]
                                 │
                                 ▼
       ┌──────────────────────── Pipeline ────────────────────────┐
       │  Request ↓                                                │
       │   ┌──────────┐   ┌──────────┐   ┌──────────┐              │
       │   │Middleware│ → │Middleware│ → │Middleware│ → …           │
       │   └──────────┘   └──────────┘   └──────────┘              │
       │         │                         │                       │
       │         └── can short-circuit with Response ──────────────┘
       │
       │
       ▼
      [ Router ] ----> match (path + method) ----> {route, params}
           │
           ▼
 [ ControllerResolver ] → instantiate + call Controller::action
           │
           ▼
       [ Controller ]
           │
           ├──→ [ Composants utilitaires ] (option)
           │
           ▼
       [ Services ]
           │
           ▼
      [ Repository ]
           │
           ▼
       creates Response (S0)

       Response ↑ flows back through Middlewares
       (headers added, timing, etc.)

                                 │
                                 ▼
                             [ Kernel ]
                                 │
                                 ▼
                           send Response
                                 │
                                 ▼
                  ┌──────────────────────────┐
        Client ←  │   HTTP Response (200 OK) │
                  └──────────────────────────┘

   [ ErrorHandler ] intercepts any Throwable at any stage
        → builds Response (404 / 500 / 405 + Allow)
``` 
