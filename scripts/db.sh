#!/usr/bin/env bash
# Dev-only tooling. Production MariaDB is Hostinger's; this script never touches it.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

MARIADB_VERSION="11.8.8"
MARIADB_TARBALL="mariadb-${MARIADB_VERSION}-linux-systemd-x86_64.tar.gz"
MARIADB_URL="https://archive.mariadb.org/mariadb-${MARIADB_VERSION}/bintar-linux-systemd-x86_64/${MARIADB_TARBALL}"
MARIADB_SHA256="fbd4e8d05946c974b0560c94f01ce4c687291b6528074c2e001fb8f35c0d2762"

DB_DIR="${ROOT_DIR}/.mariadb"
SERVER_DIR="${DB_DIR}/server"
DATA_DIR="${DB_DIR}/data"
PID_FILE="${DB_DIR}/mariadb.pid"
LOG_FILE="${DB_DIR}/mariadb.log"
PORT="${MARIADB_PORT:-3306}"

# AF_UNIX socket paths are capped at 107 characters (sockaddr_un.sun_path is a
# 108-byte buffer). Keep the socket in /tmp: a deeply nested clone of this repo
# could exceed that on its own.
SOCKET="/tmp/safeapp-pe-mariadb.sock"

ENV_FILE="${ROOT_DIR}/public/SAFEAPP/.env"
if [ -f "$ENV_FILE" ]; then
  set -a
  # shellcheck disable=SC1090
  source "$ENV_FILE"
  set +a
fi

bin() { echo "${SERVER_DIR}/bin/$1"; }
mdb() { "$(bin mariadb)" --socket="$SOCKET" -u root "$@"; }

cmd_install() {
  if [ -x "$(bin mariadbd)" ]; then
    return
  fi

  mkdir -p "$DB_DIR"
  local tarball="${DB_DIR}/${MARIADB_TARBALL}"

  echo "Downloading MariaDB ${MARIADB_VERSION}..."
  curl -fL --progress-bar -o "$tarball" "$MARIADB_URL"

  echo "Verifying checksum..."
  echo "${MARIADB_SHA256}  ${tarball}" | sha256sum -c -

  echo "Extracting to ${SERVER_DIR}..."
  rm -rf "$SERVER_DIR"
  mkdir -p "$SERVER_DIR"
  tar -xzf "$tarball" -C "$SERVER_DIR" --strip-components=1
  rm -f "$tarball"
}

cmd_init() {
  cmd_install
  if [ -d "$DATA_DIR" ] && [ -n "$(ls -A "$DATA_DIR" 2>/dev/null)" ]; then
    return
  fi

  echo "Initializing datadir at ${DATA_DIR}..."
  mkdir -p "$DATA_DIR"
  "${SERVER_DIR}/scripts/mariadb-install-db" \
    --no-defaults \
    --basedir="$SERVER_DIR" \
    --datadir="$DATA_DIR" \
    --auth-root-authentication-method=normal \
    >/dev/null
}

is_running() {
  [ -f "$PID_FILE" ] && kill -0 "$(cat "$PID_FILE")" 2>/dev/null
}

# mysqli's literal "localhost" host resolves to the system's default socket,
# not this instance's, so .env forces 127.0.0.1. The localhost grant stays too,
# for direct CLI use.
ensure_app_user() {
  : "${DB_NAME:?DB_NAME not set (check public/SAFEAPP/.env)}"
  : "${DB_USER:?DB_USER not set}"
  : "${DB_PASS:?DB_PASS not set}"
  mdb -e "
    CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
    CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
    CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
    GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
    GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
    FLUSH PRIVILEGES;
  "
}

cmd_start() {
  cmd_init
  if is_running; then
    echo "MariaDB already running (pid $(cat "$PID_FILE"))"
  else
    echo "Starting MariaDB on 127.0.0.1:${PORT}..."
    "$(bin mariadbd)" \
      --no-defaults \
      --basedir="$SERVER_DIR" \
      --datadir="$DATA_DIR" \
      --socket="$SOCKET" \
      --pid-file="$PID_FILE" \
      --port="$PORT" \
      --bind-address=127.0.0.1 \
      >"$LOG_FILE" 2>&1 &
    disown

    local ready=""
    for _ in $(seq 1 60); do
      if mdb -e "SELECT 1" >/dev/null 2>&1; then
        ready=1
        break
      fi
      sleep 0.5
    done

    if [ -z "$ready" ]; then
      echo "MariaDB failed to start; see ${LOG_FILE}" >&2
      exit 1
    fi
    echo "MariaDB started (pid $(cat "$PID_FILE"))"
  fi

  ensure_app_user
}

cmd_stop() {
  if is_running; then
    local pid
    pid="$(cat "$PID_FILE")"
    "$(bin mariadb-admin)" --socket="$SOCKET" -u root shutdown 2>/dev/null || kill "$pid" 2>/dev/null || true
    for _ in $(seq 1 20); do
      kill -0 "$pid" 2>/dev/null || break
      sleep 0.5
    done
    rm -f "$PID_FILE" "$SOCKET"
    echo "MariaDB stopped"
  else
    echo "MariaDB is not running"
  fi
}

DEV_ADMIN_USER="dev.admin"
DEV_ADMIN_PASS="devpassword"

# The dump's real accounts carry unknown production password hashes (MD5, no
# recovery). Seed a known-password admin instead of guessing at one.
#
# vista/principal.php reads asigna (user -> tienda) unconditionally on login,
# with no null check, so every user needs a row there too or PHP throws
# "Trying to access array offset on value of type null" warnings. Real
# ADMINISTRADOR accounts are assigned id_tienda=4 (REGION LIMA); match that.
seed_dev_admin() {
  mdb -D "$DB_NAME" -e "
    DELETE FROM acceso WHERE id_usuario IN (SELECT id_usuario FROM usuario WHERE usuario = '${DEV_ADMIN_USER}');
    DELETE FROM asigna WHERE id_usuario IN (SELECT id_usuario FROM usuario WHERE usuario = '${DEV_ADMIN_USER}');
    DELETE FROM usuario WHERE usuario = '${DEV_ADMIN_USER}';
    INSERT INTO usuario
      (usuario, clave, id_rol, personal, estado, sexo, id_supervisor, fecha_nacimiento, telefono, correo)
      VALUES ('${DEV_ADMIN_USER}', MD5('${DEV_ADMIN_PASS}'), 1, 'DEV ADMIN', 1, 'M', 1, '0000-00-00', '', '');
    INSERT INTO acceso (id_menu, id_usuario)
      SELECT id_menu, LAST_INSERT_ID() FROM accesoxrol WHERE id_rol = 1;
    INSERT INTO asigna (id_tienda, id_usuario, estado)
      VALUES (4, LAST_INSERT_ID(), 0);
  "
  echo "Dev admin ready: ${DEV_ADMIN_USER} / ${DEV_ADMIN_PASS}"
}

cmd_restore() {
  local file="${1:-${ROOT_DIR}/127_0_0_1.sql}"
  [ -f "$file" ] || { echo "File not found: $file" >&2; exit 1; }
  : "${DB_NAME:?DB_NAME not set (check public/SAFEAPP/.env)}"
  cmd_start
  echo "Restoring ${file}..."
  mdb -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
  # 127_0_0_1.sql carries its own CREATE DATABASE/USE for the Hostinger
  # account name. Strip those so restore always targets DB_NAME instead.
  grep -v -E '^(CREATE DATABASE|USE `)' "$file" | mdb -D "$DB_NAME"
  # 127_0_0_1.sql isn't version-controlled (see .gitignore), so a schema fix
  # committed here never reaches it. See migrations/2026-07-03-widen-usuario-clave.sql
  # for why: password_hash() output (60+ chars) doesn't fit the dump's varchar(50).
  mdb -D "$DB_NAME" -e "ALTER TABLE usuario MODIFY clave VARCHAR(255) NOT NULL;"
  seed_dev_admin
  echo "Done"
}

cmd_dump() {
  local file="${1:-${ROOT_DIR}/dev-dump.sql}"
  : "${DB_NAME:?DB_NAME not set}"
  cmd_start
  "$(bin mariadb-dump)" --socket="$SOCKET" -u root "$DB_NAME" > "$file"
  echo "Dumped to ${file}"
}

cmd_reset() {
  local file="${1:-${ROOT_DIR}/127_0_0_1.sql}"
  cmd_stop
  rm -rf "$DATA_DIR"
  cmd_restore "$file"
}

cmd_status() {
  if is_running; then
    echo "running (pid $(cat "$PID_FILE"), socket ${SOCKET})"
  else
    echo "stopped"
  fi
}

case "${1:-}" in
  install) cmd_install ;;
  init)    cmd_init ;;
  start)   cmd_start ;;
  stop)    cmd_stop ;;
  restore) shift; cmd_restore "${1:-}" ;;
  dump)    shift; cmd_dump "${1:-}" ;;
  reset)   shift; cmd_reset "${1:-}" ;;
  status)  cmd_status ;;
  *)
    echo "Usage: $0 {install|init|start|stop|restore [file]|dump [file]|reset [file]|status}" >&2
    exit 1
    ;;
esac
