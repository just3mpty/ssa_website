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

phpstan:
	@echo "Phpstan analyse"
	vendor/bin/phpstan analyse lib src --level=6

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

open-doc:
	xdg-open doc.md

# --- (Facultatif) Bash dans les containers ---
bash-db:
	$(DC) exec db bash

bash-web:
	$(DC) exec web bash


.PHONY: up down logs db-purge restart pma pma-stop open-pma open-web open-doc bash-db bash-web
