.DEFAULT_GOAL := help
.PHONY: help setup dev pint pint-check test setup-legacy db-up db-down db-restore db-dump db-reset db-status fmt fmt-check lint

MISE := mise exec --
DB := ./scripts/db.sh
FILE ?=

help: ## Show this help
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

setup: db-up ## Install the Laravel app's deps, prepare .env, build a fresh dev DB
	$(MISE) composer install
	@[ -f .env ] || cp .env.example .env
	$(MISE) php artisan key:generate --ansi
	$(MISE) php artisan migrate --seed --ansi

dev: db-up ## Start the local DB and the Laravel dev server
	$(MISE) php artisan serve

pint: ## Format the Laravel app's PHP code
	$(MISE) vendor/bin/pint

pint-check: ## Check the Laravel app's PHP formatting without modifying files (for CI)
	$(MISE) vendor/bin/pint --test

test: ## Run the Laravel app's test suite
	$(MISE) php artisan test

setup-legacy: ## Legacy snapshot capture: install legacy deps and load a real dump (old schema)
	$(MISE) composer install --working-dir=public/SAFEAPP
	$(DB) restore $(FILE)

db-up: ## Start the local MariaDB instance (installs/initializes on first run)
	$(DB) start

db-down: ## Stop the local MariaDB instance
	$(DB) stop

db-restore: ## Load a .sql dump into the local DB for legacy snapshot capture (FILE=path)
	$(DB) restore $(FILE)

db-dump: ## Snapshot the local DB to a .sql file (FILE=path, default dev-dump.sql)
	$(DB) dump $(FILE)

db-reset: db-up ## Wipe the dev DB and rebuild it from migrations + seeders
	$(MISE) php artisan migrate:fresh --seed --ansi

fmt: ## Format the legacy app's PHP code with Mago
	$(MISE) mago format

fmt-check: ## Check the legacy app's PHP formatting without modifying files (for CI)
	$(MISE) mago format --check

lint: ## Lint the legacy app's PHP code with Mago
	$(MISE) mago lint

db-status: ## Show whether the local MariaDB instance is running
	$(DB) status
