PROJECT_NAME := ssa_website

.PHONY: all build up down logs exec bash init-db prune

all: build up

## 🔨 Build l'image Docker (php + apache + deps)
build:
	docker compose build

## 🚀 Lance les services (detached)
up:
	docker compose up -d

## ⏹️ Arrête les conteneurs
down:
	docker compose down

## 📋 Affiche les logs
logs:
	docker compose logs -f

## 🐚 Ouvre un shell bash dans le conteneur web
bash:
	docker compose exec web bash

## 💻 Lance un shell dans le conteneur web (user shell par défaut)
exec:
	docker compose exec web sh

## 🧪 Init DB (via le conteneur pour être reproductible !)
init-db:
	docker compose run --rm web bash -c "sqlite3 /var/www/html/data/database.sqlite < /var/www/html/migrations/tables.sql"
