DC = docker compose

# --- Commandes principales ---
up:
	$(DC) up -d

down:
	$(DC) down

logs:
	$(DC) logs -f

db-purge:
	$(DC) down -v

restart: down up

# --- Lancer seulement phpMyAdmin (avec dépendances) ---
pma:
	$(DC) up -d pma

pma-stop:
	$(DC) stop pma

# --- Accès rapides ---
open-pma:
	xdg-open http://localhost:8081

open-web:
	xdg-open http://localhost:8080

# --- (Facultatif) Bash dans les containers ---
bash-db:
	$(DC) exec db bash

bash-web:
	$(DC) exec web bash

# --- (Ajoute ici d'autres outils, ex : adminer) ---

.PHONY: up down logs db-purge restart pma pma-stop open-pma open-web bash-db bash-web
