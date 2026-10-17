#!/usr/bin/env bash
# Create .env from .env.example with a random database password and application
# key. Preserve an existing .env so its credentials remain stable. The database
# helper reads its name and credentials from .env, so the file must exist first.
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

if [ ! -f .env ]; then
  sed "s|^DB_PASSWORD=.*|DB_PASSWORD=$(openssl rand -hex 16)|" .env.example > .env
  php artisan key:generate --ansi
fi
