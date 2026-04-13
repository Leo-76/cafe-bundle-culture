#!/bin/bash
# =============================================================================
# setup-demo-app.sh
# Cree un projet Symfony 8 complet pour tester cafe-culture/blog-bundle
# Usage : bash setup-demo-app.sh
# =============================================================================

set -e

APP_NAME="cafe-demo"
BUNDLE_PATH="$(pwd)"   # chemin vers le dossier blog-bundle (adapter si besoin)

echo ""
echo "============================================"
echo "  Installation du projet demo Symfony 8"
echo "============================================"
echo ""

# ── 1. Creer le projet Symfony skeleton ──────────────────────────────────────
echo "[1/8] Creation du projet Symfony 8..."
composer create-project symfony/skeleton:"8.0.*" "$APP_NAME" --no-interaction
cd "$APP_NAME"

# ── 2. Installer les dependances web essentielles ────────────────────────────
echo ""
echo "[2/8] Installation des dependances web..."
composer require webapp --no-interaction

# ── 3. Ajouter le depot local du bundle (path repository) ────────────────────
echo ""
echo "[3/8] Ajout du depot local du bundle..."
composer config repositories.cafe-culture \
  '{"type": "path", "url": "'"$BUNDLE_PATH"'", "options": {"symlink": false}}'

# ── 4. Installer le bundle ────────────────────────────────────────────────────
echo ""
echo "[4/8] Installation de cafe-culture/blog-bundle..."
composer require cafe-culture/blog-bundle:* --no-interaction

# ── 5. Activer le bundle manuellement si Flex ne le fait pas ─────────────────
echo ""
echo "[5/8] Activation du bundle..."
php -r "
\$file = 'config/bundles.php';
\$content = file_get_contents(\$file);
\$line = \"    CafeCulture\\\\BlogBundle\\\\CafeCultureBundle::class => ['all' => true],\n\";
if (strpos(\$content, 'CafeCultureBundle') === false) {
    \$content = str_replace(\"return [\n\", \"return [\n\" . \$line, \$content);
    file_put_contents(\$file, \$content);
    echo 'Bundle active dans config/bundles.php' . PHP_EOL;
} else {
    echo 'Bundle deja present dans config/bundles.php' . PHP_EOL;
}
"

# ── 6. Lancer la commande d'installation du bundle ───────────────────────────
echo ""
echo "[6/8] Installation des fichiers du bundle (config, routes, assets)..."
php bin/console cafe:install --no-migrate --no-interaction || true

# ── 7. Configurer la base de donnees SQLite pour le dev ──────────────────────
echo ""
echo "[7/8] Configuration base de donnees SQLite..."
cat > .env.local << 'ENV'
APP_ENV=dev
APP_SECRET=mon_secret_local_cafe_culture_2024
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
ENV

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# ── 8. Charger les fixtures de demo ──────────────────────────────────────────
echo ""
echo "[8/8] Chargement des donnees de demonstration..."
php bin/console doctrine:fixtures:load --no-interaction --append || \
  echo "(fixtures ignorees si doctrine/doctrine-fixtures-bundle non installe)"

# ── Resultat ──────────────────────────────────────────────────────────────────
echo ""
echo "============================================"
echo "  Installation terminee !"
echo "============================================"
echo ""
echo "  Demarrer le serveur :"
echo "    cd $APP_NAME"
echo "    symfony serve"
echo ""
echo "  Puis ouvrir :"
echo "    http://127.0.0.1:8000/blog"
echo "    http://127.0.0.1:8000/admin/cafe  (ROLE_ADMIN requis)"
echo "    http://127.0.0.1:8000/blog/sitemap.xml"
echo ""
