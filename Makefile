.DEFAULT_GOAL := help
.PHONY: help setup dev pint pint-check test setup-legacy db-up db-down db-restore db-dump db-reset db-status fmt fmt-check lint

MISE := mise exec --
DB := ./scripts/db.sh
FILE ?=

help: ## Show this help
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

setup: ## Install the Laravel app's dependencies and prepare its .env
	$(MISE) composer install
	@[ -f .env ] || cp .env.example .env
	$(MISE) php artisan key:generate --ansi
	$(MISE) php artisan migrate --graceful --ansi

dev: db-up ## Start the local DB and the Laravel dev server (Laravel at /, legacy at /SAFEAPP/)
	$(MISE) php artisan serve

pint: ## Format the Laravel app's PHP code
	$(MISE) vendor/bin/pint

pint-check: ## Check the Laravel app's PHP formatting without modifying files (for CI)
	$(MISE) vendor/bin/pint --test

test: ## Run the Laravel app's test suite
	$(MISE) php artisan test

setup-legacy: ## Install the legacy app's PHP deps, start the local DB, and seed it from 127_0_0_1.sql
	$(MISE) composer install --working-dir=public/SAFEAPP
	$(DB) restore $(FILE)

db-up: ## Start the local MariaDB instance (installs/initializes on first run)
	$(DB) start

db-down: ## Stop the local MariaDB instance
	$(DB) stop

db-restore: ## Import a .sql file into the local DB (FILE=path, default 127_0_0_1.sql)
	$(DB) restore $(FILE)

db-dump: ## Snapshot the local DB to a .sql file (FILE=path, default dev-dump.sql)
	$(DB) dump $(FILE)

db-reset: ## Wipe the local DB and reseed from 127_0_0_1.sql (FILE=path to override)
	$(DB) reset $(FILE)

fmt: ## Format the legacy app's PHP code with Mago
	$(MISE) mago format

fmt-check: ## Check the legacy app's PHP formatting without modifying files (for CI)
	$(MISE) mago format --check

lint: ## Lint the legacy app's PHP code with Mago
	$(MISE) mago lint

db-status: ## Show whether the local MariaDB instance is running
	$(DB) status
