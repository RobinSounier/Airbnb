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

if [ ! -f "$BACKUP_FILE" ]; then
  echo "Erreur : Le fichier de sauvegarde $BACKUP_FILE n'existe pas." >&2
  echo "Vérifiez que le fichier existe dans le répertoire ./db/ sur l'hôte." >&2
  exit 1
fi

if [ ! -s "$BACKUP_FILE" ]; then
  echo "Erreur : Le fichier de sauvegarde $BACKUP_FILE est vide." >&2
  exit 1
fi

echo "Attention : Cette opération va écraser la base de données $MYSQL_DATABASE"
echo "Fichier de restauration : $BACKUP_FILE"

if mariadb "$MYSQL_DATABASE" -uroot -p"$MYSQL_ROOT_PASSWORD" < "$BACKUP_FILE"; then
  echo "✓ Restauration terminée avec succès depuis : $BACKUP_FILE"
else
  echo "✗ Erreur lors de la restauration" >&2
  exit 1
fi

