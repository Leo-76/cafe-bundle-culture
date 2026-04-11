#!/usr/bin/env bash
# ============================================================
# Script de test local du bundle dans un projet Symfony
# Usage : bash docs/test-local.sh /chemin/vers/votre/projet
# ============================================================

set -e

BUNDLE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PROJECT_DIR="${1:-}"

if [ -z "$PROJECT_DIR" ]; then
    echo "Usage : bash docs/test-local.sh /chemin/vers/votre/projet-symfony"
    exit 1
fi

if [ ! -f "$PROJECT_DIR/composer.json" ]; then
    echo "ERREUR : $PROJECT_DIR ne semble pas etre un projet Symfony (pas de composer.json)"
    exit 1
fi

echo ""
echo "=== Installation locale du CafeCultureBundle ==="
echo "Bundle : $BUNDLE_DIR"
echo "Projet : $PROJECT_DIR"
echo ""

# 1. Ajouter le path repository dans le projet
cd "$PROJECT_DIR"

python3 - << PYTHON
import json, sys

with open('composer.json', 'r') as f:
    data = json.load(f)

repos = data.get('repositories', [])

# Eviter les doublons
already = any(r.get('url') == '$BUNDLE_DIR' for r in repos)
if not already:
    repos.insert(0, {
        'type': 'path',
        'url': '$BUNDLE_DIR',
        'options': {'symlink': True}
    })
    data['repositories'] = repos
    print('[OK] Path repository ajoute')
else:
    print('[SKIP] Path repository deja present')

# minimum-stability
if data.get('minimum-stability') not in ('dev', 'alpha', 'beta', 'RC'):
    data['minimum-stability'] = 'dev'
    data['prefer-stable'] = True
    print('[OK] minimum-stability: dev ajoute')

with open('composer.json', 'w') as f:
    json.dump(data, f, indent=4, ensure_ascii=False)
PYTHON

# 2. Installer le bundle
echo ""
echo "==> composer require cafe-culture/blog-bundle:@dev"
composer require cafe-culture/blog-bundle:@dev --no-interaction

# 3. Lancer l'installation Symfony
echo ""
echo "==> php bin/console cafe:install --fixtures"
php bin/console cafe:install --fixtures --no-interaction

# 4. Verification
echo ""
echo "==> php bin/console cafe:check"
php bin/console cafe:check

echo ""
echo "=== Installation terminee ! ==="
echo "Visitez : http://localhost/blog"
echo "Admin   : http://localhost/admin/cafe"
