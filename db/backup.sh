#!/usr/bin/sh
set -euo pipefail

if [ -z "${MYSQL_DATABASE:-}" ]; then
  echo "Erreur : La variable d'environnement MYSQL_DATABASE n'est pas définie." >&2
  exit 1
fi

if [ -z "${MYSQL_ROOT_PASSWORD:-}" ]; then
  echo "Erreur : La variable d'environnement MYSQL_ROOT_PASSWORD n'est pas définie." >&2
  exit 1
fi

BACKUP_DIR="/docker-entrypoint-initdb.d"
BACKUP_FILE="${BACKUP_DIR}/init.sql"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE_TIMESTAMPED="${BACKUP_DIR}/init_${TIMESTAMP}.sql"

if [ -f "$BACKUP_FILE" ]; then
  cp "$BACKUP_FILE" "$BACKUP_FILE_TIMESTAMPED"
  echo "Ancienne sauvegarde copiée vers : $BACKUP_FILE_TIMESTAMPED"
fi

if mariadb-dump "$MYSQL_DATABASE" -uroot -p"$MYSQL_ROOT_PASSWORD" > "$BACKUP_FILE"; then
  echo "✓ Sauvegarde terminée avec succès : $BACKUP_FILE"
  if command -v du >/dev/null 2>&1; then
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "  Taille : $SIZE"
  fi
else
  echo "✗ Erreur lors de la sauvegarde" >&2
  exit 1
fi

