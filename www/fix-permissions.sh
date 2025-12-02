#!/bin/bash

# ============================================
# SCRIPT DE CORRECTION DES PERMISSIONS (Docker)
# ============================================
# 
# Ce script fixe les permissions des dossiers critiques
# pour que l'application fonctionne correctement sous Linux.
#
# Usage: ./fix-permissions.sh
# Ou depuis le container: docker compose exec apache_app bash fix-permissions.sh

set -e

echo "🔧 Correction des permissions..."

# Dossiers qui doivent être accessibles en écriture
STORAGE_LOGS="storage/logs"
PUBLIC_UPLOADS="public/uploads"

# Créer les dossiers s'ils n'existent pas
mkdir -p "$STORAGE_LOGS"
mkdir -p "$PUBLIC_UPLOADS"

# Fixer les permissions (755 = rwxr-xr-x)
chmod -R 755 "$STORAGE_LOGS"
chmod -R 755 "$PUBLIC_UPLOADS"

# Si on est dans le container Docker, changer le propriétaire en www-data
if [ -n "$DOCKER_CONTAINER" ] || [ -f /.dockerenv ]; then
    echo "🐳 Détection Docker - Changement du propriétaire en www-data..."
    chown -R www-data:www-data "$STORAGE_LOGS" 2>/dev/null || echo "⚠️  Impossible de changer le propriétaire (nécessite sudo)"
    chown -R www-data:www-data "$PUBLIC_UPLOADS" 2>/dev/null || echo "⚠️  Impossible de changer le propriétaire (nécessite sudo)"
else
    # Si on est sur l'hôte Linux, utiliser l'utilisateur actuel
    CURRENT_USER=$(whoami)
    echo "👤 Utilisation de l'utilisateur actuel: $CURRENT_USER"
    chown -R "$CURRENT_USER:$CURRENT_USER" "$STORAGE_LOGS" 2>/dev/null || echo "⚠️  Impossible de changer le propriétaire (nécessite sudo)"
    chown -R "$CURRENT_USER:$CURRENT_USER" "$PUBLIC_UPLOADS" 2>/dev/null || echo "⚠️  Impossible de changer le propriétaire (nécessite sudo)"
fi

echo "✅ Permissions corrigées avec succès!"
echo ""
echo "📝 Dossiers corrigés:"
echo "   - $STORAGE_LOGS (755)"
echo "   - $PUBLIC_UPLOADS (755)"