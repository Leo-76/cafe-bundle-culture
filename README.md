# CafeCultureBundle

[![Packagist Version](https://img.shields.io/packagist/v/cafe-culture/blog-bundle)](https://packagist.org/packages/cafe-culture/blog-bundle)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue)](https://php.net)
[![Symfony](https://img.shields.io/badge/symfony-6.4%20%7C%207.x%20%7C%208.x-black)](https://symfony.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

**CafeCultureBundle** est un bundle Symfony clé-en-main pour créer un blog dédié à la culture du café.
Il fournit toutes les entités, contrôleurs, templates, assets et fixtures nécessaires pour démarrer immédiatement.

> **Important** — Ce dépôt contient uniquement le **bundle** (composant réutilisable).
> Pour tester localement, utilisez une application Symfony dédiée.
> Ne lancez pas `symfony serve` depuis ce dossier : un bundle n'a pas de `public/index.php`.

---

## Fonctionnalités

- **Blog complet** : articles, catégories, tags, commentaires avec modération
- **Champs spécialisés café** : origine géographique, profil aromatique, temps de lecture
- **Notation par étoiles** (AJAX, une note par IP)
- **Recherche full-text** avec autocomplétion AJAX
- **API REST légère** : `/api/cafe/search`, `/api/cafe/recent`, `/api/cafe/rate/{id}`
- **Sitemap XML** et **flux RSS** générés automatiquement
- **Extension Twig** : filtres `reading_time`, `excerpt`, `stars` ; fonctions `coffee_flag`, `aroma_badge`
- **Back-office** : dashboard, CRUD articles/catégories, modération commentaires (`ROLE_ADMIN`)
- **Thème CSS** responsive aux couleurs café + thème admin séparé
- **6 catégories prédéfinies** : Origines, Torréfaction, Préparations, Recettes, Histoire, Matériel
- **Fixtures** : 6 articles rédigés, 15 tags, 6 catégories prêts à l'emploi
- **Migration Doctrine** incluse (6 tables)
- **3 commandes console** : `cafe:install`, `cafe:check`, `cafe:uninstall`

---

## Prérequis

| Logiciel | Version |
|----------|---------|
| PHP | **8.2** ou supérieur |
| Symfony | **6.4**, **7.x** ou **8.x** |
| doctrine/doctrine-bundle | 2.11 ou supérieur |
| doctrine/orm | 2.15 ou 3.x |

---

## Installation dans un projet Symfony existant

### Etape 1 — Installer via Composer

```bash
composer require cafe-culture/blog-bundle
```

### Etape 2 — Activer le bundle

Si Symfony Flex ne l'a pas ajouté automatiquement, ajoutez-le dans `config/bundles.php` :

```php
// config/bundles.php
return [
    // ...
    CafeCulture\BlogBundle\CafeCultureBundle::class => ['all' => true],
];
```

### Etape 3 — Déclarer les routes

Créez le fichier `config/routes/cafe_culture.yaml` :

```yaml
cafe_culture_blog:
    resource: '@CafeCultureBlogBundle/src/Controller/BlogController.php'
    type: attribute
    prefix: /blog

cafe_culture_api:
    resource: '@CafeCultureBlogBundle/src/Controller/ApiController.php'
    type: attribute
    prefix: /api/cafe

cafe_culture_sitemap:
    resource: '@CafeCultureBlogBundle/src/Controller/SitemapController.php'
    type: attribute

cafe_culture_admin:
    resource: '@CafeCultureBlogBundle/src/Controller/AdminController.php'
    type: attribute
    prefix: /admin/cafe
```

### Etape 4 — Copier la configuration du bundle

```bash
cp vendor/cafe-culture/blog-bundle/config/packages/cafe_culture.yaml config/packages/
```

### Etape 5 — Configurer Doctrine

Dans `config/packages/doctrine.yaml`, ajoutez le mapping du bundle :

```yaml
doctrine:
    orm:
        mappings:
            CafeCultureBlogBundle:
                is_bundle: true
                type: attribute
                dir: 'src/Entity'
                prefix: 'CafeCulture\BlogBundle\Entity'
                alias: CafeCulture

doctrine_migrations:
    migrations_paths:
        'CafeCulture\BlogBundle\Migrations': '%kernel.project_dir%/vendor/cafe-culture/blog-bundle/migrations'
        'DoctrineMigrations': '%kernel.project_dir%/migrations'
```

### Etape 6 — Lancer l'installation

```bash
# Tout en une commande
php bin/console cafe:install --fixtures

# Vérifier que tout est correct
php bin/console cafe:check
```

Ou étape par étape :

```bash
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console assets:install
php bin/console doctrine:fixtures:load --append   # optionnel, données de démo
```

---

## Développement local (sans Packagist)

Un bundle ne se lance pas directement. Il faut l'intégrer dans une application Symfony.
Utilisez un **path repository** pour travailler sur le bundle et le tester simultanément :

```
votre-workspace/
├── blog-bundle/      <- ce dépôt (le composant)
└── demo-app/         <- application Symfony de test
```

`demo-app/composer.json` :

```json
{
    "repositories": {
        "cafe-culture-local": {
            "type": "path",
            "url": "../blog-bundle",
            "options": { "symlink": false }
        }
    },
    "require": {
        "cafe-culture/blog-bundle": "*"
    }
}
```

```bash
cd demo-app
composer install
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction --append
symfony serve
# Ouvrir : http://127.0.0.1:8000/blog
```

---

## Publication sur Packagist

Packagist détecte les versions **exclusivement depuis les tags Git**.
Le champ `"version"` ne doit **pas** exister dans `composer.json`.

```bash
# Première publication
git init
git add .
git commit -m "feat: initial release"
git remote add origin git@github.com:cafe-culture/blog-bundle.git
git push -u origin main

# Créer un tag stable (obligatoire pour que Packagist voie une version stable)
git tag v1.1.0
git push origin v1.1.0

# Soumettre l'URL sur https://packagist.org/packages/submit
# Puis dans les projets :
composer require cafe-culture/blog-bundle
```

Pour les versions suivantes :

```bash
git tag v1.2.0
git push origin v1.2.0
# Packagist se met à jour automatiquement si le webhook GitHub est configuré
```

Voir [RELEASING.md](RELEASING.md) pour le guide complet de publication et la configuration du webhook.

---

## Configuration

```yaml
# config/packages/cafe_culture.yaml
cafe_culture:
    posts_per_page: 9                # Articles par page (défaut : 9)
    upload_dir: '%kernel.project_dir%/public/uploads/cafe-culture'
    default_locale: 'fr'
    enable_comments: true            # Activer les commentaires
    enable_ratings: true             # Activer la notation par étoiles
    sitename: 'La Culture du Cafe'
```

---

## Commandes console

```bash
# Installer le bundle dans un projet Symfony
php bin/console cafe:install
php bin/console cafe:install --fixtures      # + articles/catégories de démo
php bin/console cafe:install --force         # écraser les fichiers existants
php bin/console cafe:install --no-migrate    # sauter la migration Doctrine
php bin/console cafe:install --no-assets     # sauter la copie des assets

# Vérifier l'installation (tables BDD, routes, assets, config)
php bin/console cafe:check

# Désinstaller proprement
php bin/console cafe:uninstall
php bin/console cafe:uninstall --drop-tables  # + supprimer les tables BDD

# Statistiques du blog
php bin/console cafe:articles:stats
php bin/console cafe:articles:stats --detailed

# Gérer les brouillons
php bin/console cafe:articles:publish --list    # lister tous les brouillons
php bin/console cafe:articles:publish --id=5    # publier l'article #5
php bin/console cafe:articles:publish --all     # publier tous les brouillons
```

---

## Structure des routes

| Route | URL | Description |
|-------|-----|-------------|
| `cafe_culture_index` | `/blog` | Page d'accueil du blog |
| `cafe_culture_article_show` | `/blog/article/{slug}` | Afficher un article |
| `cafe_culture_category` | `/blog/categorie/{slug}` | Articles par catégorie |
| `cafe_culture_tag` | `/blog/tag/{slug}` | Articles par tag |
| `cafe_culture_search` | `/blog/recherche?q=...` | Recherche |
| `cafe_culture_origin` | `/blog/origine/{origin}` | Articles par origine café |
| `cafe_culture_sitemap` | `/blog/sitemap.xml` | Sitemap XML pour le SEO |
| `cafe_culture_rss` | `/blog/rss.xml` | Flux RSS |
| `cafe_culture_admin_dashboard` | `/admin/cafe` | Tableau de bord admin |
| `cafe_culture_api_rate` | `POST /api/cafe/rate/{id}` | Noter un article |
| `cafe_culture_api_recent` | `GET /api/cafe/recent` | Articles récents (JSON) |
| `cafe_culture_api_search` | `GET /api/cafe/search?q=...` | Recherche AJAX (JSON) |

---

## Extension Twig

### Variables globales disponibles dans tous les templates

```twig
{{ cafe_categories }}    {# Toutes les catégories ordonnées #}
{{ cafe_recent }}        {# 5 derniers articles publiés #}
{{ cafe_popular_tags }}  {# 15 tags les plus utilisés #}
{{ cafe_most_viewed }}   {# 5 articles les plus vus #}
```

### Filtres

```twig
{{ article.content|reading_time }}      {# "7 min de lecture" #}
{{ article.content|excerpt(160) }}      {# Extrait tronqué proprement #}
{{ article.averageRating|stars }}       {# Etoiles HTML #}
```

### Fonctions

```twig
{{ coffee_flag('Ethiopie') }}           {# Drapeau avec tooltip #}
{{ aroma_badge('chocolaté') }}          {# Badge coloré #}
```

---

## Entités

### Article

| Champ | Type | Description |
|-------|------|-------------|
| `title` | string | Titre de l'article |
| `slug` | string | URL (auto-généré depuis le titre) |
| `content` | text | Corps de l'article |
| `excerpt` | text | Extrait (auto-généré si vide) |
| `status` | string | `draft`, `published`, `archived` |
| `featured` | bool | Article mis en avant dans le hero |
| `coffeeOrigin` | string | Origine géographique du café |
| `aromaProfile` | JSON | Profil aromatique (tableau de strings) |
| `readingTimeMinutes` | int | Temps de lecture (auto-calculé) |
| `coverImage` | string | Nom de fichier de l'image de couverture |
| `views` | int | Compteur de vues (anti-doublon par cookie) |
| `metaTitle` | string | Titre SEO (auto-généré si vide) |
| `metaDescription` | string | Description SEO (auto-générée si vide) |

---

## API REST

### Noter un article

```http
POST /api/cafe/rate/{id}
Content-Type: application/json

{"score": 4}
```

Réponse :
```json
{ "success": true, "average": 4.2, "count": 15 }
```

### Recherche AJAX

```http
GET /api/cafe/search?q=espresso
```

Réponse :
```json
[
  {
    "id": 1,
    "title": "L'espresso parfait",
    "slug": "espresso-parfait",
    "excerpt": "Guide complet pour réaliser...",
    "category": "Methodes de Preparation"
  }
]
```

---

## Surcharge des templates

```bash
mkdir -p templates/bundles/CafeCultureBlogBundle
cp -r vendor/cafe-culture/blog-bundle/templates/* templates/bundles/CafeCultureBlogBundle/
```

Structure surchargeable :

```
templates/bundles/CafeCultureBlogBundle/
├── layout/
│   └── base.html.twig
├── blog/
│   ├── index.html.twig
│   ├── show.html.twig
│   ├── category.html.twig
│   ├── tag.html.twig
│   ├── search.html.twig
│   └── origin.html.twig
├── admin/
│   ├── dashboard.html.twig
│   ├── articles/
│   ├── categories/
│   └── comments/
└── partials/
    ├── article_card.html.twig
    ├── sidebar.html.twig
    └── pagination.html.twig
```

---

## Packages optionnels

Ces packages enrichissent le bundle mais ne sont pas obligatoires :

```bash
composer require twig/markdown-extra michelf/php-markdown   # rendu Markdown
composer require vich/uploader-bundle                        # upload images avancé
composer require liip/imagine-bundle                         # redimensionnement images
composer require knplabs/knp-paginator-bundle                # pagination avancée
composer require doctrine/doctrine-fixtures-bundle           # charger les fixtures
```

---

## Lancer les tests

```bash
composer install
vendor/bin/phpunit --testdox
```

---

## Contribuer

Les contributions sont les bienvenues. Merci d'ouvrir une **issue** avant de soumettre une **pull request**.

1. Forkez le dépôt
2. Créez une branche (`git checkout -b feature/ma-fonctionnalite`)
3. Committez vos changements (`git commit -m 'feat: ajouter X'`)
4. Pushez (`git push origin feature/ma-fonctionnalite`)
5. Ouvrez une Pull Request

Voir [CONTRIBUTING.md](CONTRIBUTING.md) pour les standards de code et le guide complet.

---

## Licence

Ce bundle est distribué sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus d'informations.

---

## Auteur

**CafeCulture Team** — [hello@cafe-culture.dev](mailto:hello@cafe-culture.dev)
