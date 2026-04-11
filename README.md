# ☕ CafeCultureBundle

[![Packagist Version](https://img.shields.io/packagist/v/cafe-culture/blog-bundle)](https://packagist.org/packages/cafe-culture/blog-bundle)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)](https://php.net)
[![Symfony](https://img.shields.io/badge/symfony-6.4%20%7C%207.x-black)](https://symfony.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

**CafeCultureBundle** est un bundle Symfony clé-en-main pour créer un blog dédié à la culture du café.  
Il fournit toutes les entités, contrôleurs, templates, assets et fixtures nécessaires pour démarrer immédiatement.

---

## Fonctionnalités

- **Blog complet** : articles, catégories, tags, commentaires avec modération
- **Champs spécialisés café** : origine géographique, profil aromatique, temps de lecture
- **Notation par étoiles** (AJAX, une note par IP)
- **Recherche full-text** avec autocomplétion AJAX
- **API REST légère** : `/api/cafe/search`, `/api/cafe/recent`, `/api/cafe/rate/{id}`
- **Extension Twig** : filtres `reading_time`, `excerpt`, `stars` ; fonctions `coffee_flag`, `aroma_badge`
- **Thème CSS** élégant aux couleurs café (responsive, mobile-first)
- **6 catégories prédéfinies** : Origines, Torréfaction, Préparations, Recettes, Histoire, Matériel
- **Fixtures** : 6 articles rédigés, 15 tags, 6 catégories prêts à l'emploi
- **Migration Doctrine** incluse

---

## Prérequis

| Logiciel  | Version minimale |
|-----------|-----------------|
| PHP       | 8.1             |
| Symfony   | 6.4 ou 7.x      |
| Doctrine  | 2.15 ou 3.x     |
| Node.js   | (optionnel, pour les assets) |

---

## Installation

### 1. Installer via Composer

```bash
composer require cafe-culture/blog-bundle
```

### 2. Activer le bundle (si Flex n'est pas utilisé)

```php
// config/bundles.php
return [
    // ...
    CafeCulture\BlogBundle\CafeCultureBundle::class => ['all' => true],
];
```

### 3. Copier la configuration

```bash
cp vendor/cafe-culture/blog-bundle/config/packages/cafe_culture.yaml config/packages/
```

### 4. Importer les routes

```yaml
# config/routes/cafe_culture.yaml
cafe_culture:
    resource: '@CafeCultureBundle/config/routes.yaml'
```

### 5. Exécuter la migration

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Installer les assets

```bash
php bin/console assets:install
```

### 7. (Optionnel) Charger les fixtures de démo

```bash
php bin/console doctrine:fixtures:load
```

---

## Configuration

```yaml
# config/packages/cafe_culture.yaml
cafe_culture:
    posts_per_page: 9           # Articles par page (défaut : 9)
    upload_dir: '%kernel.project_dir%/public/uploads/cafe-culture'
    default_locale: 'fr'
    enable_comments: true       # Activer les commentaires
    enable_ratings: true        # Activer la notation par étoiles
    sitename: 'La Culture du Café'
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
| `cafe_culture_api_rate` | `POST /api/cafe/rate/{id}` | Noter un article |
| `cafe_culture_api_recent` | `GET /api/cafe/recent` | Articles récents (JSON) |
| `cafe_culture_api_search` | `GET /api/cafe/search?q=...` | Recherche AJAX (JSON) |

---

## Utilisation des templates Twig

Les templates du bundle peuvent être surchargés dans votre application :

```bash
# Copier un template pour le personnaliser
cp -r vendor/cafe-culture/blog-bundle/templates templates/bundles/CafeCultureBundle/
```

### Variables globales Twig disponibles

```twig
{{ cafe_categories }}    {# Toutes les catégories ordonnées #}
{{ cafe_recent }}        {# 5 derniers articles publiés #}
{{ cafe_popular_tags }}  {# 15 tags les plus utilisés #}
{{ cafe_most_viewed }}   {# 5 articles les plus vus #}
```

### Filtres Twig

```twig
{{ article.content|reading_time }}         {# "7 min de lecture" #}
{{ article.content|excerpt(160) }}         {# Extrait tronqué proprement #}
{{ article.averageRating|stars }}          {# ★★★★☆ en HTML #}
```

### Fonctions Twig

```twig
{{ coffee_flag('Éthiopie') }}              {# 🇪🇹 avec tooltip #}
{{ aroma_badge('chocolaté') }}             {# Badge coloré #}
```

---

## Entités

### Article

| Champ | Type | Description |
|-------|------|-------------|
| `title` | string | Titre de l'article |
| `slug` | string | URL (auto-généré) |
| `content` | text | Corps de l'article |
| `excerpt` | text | Extrait (optionnel) |
| `status` | string | `draft`, `published`, `archived` |
| `featured` | bool | Article mis en avant |
| `coffeeOrigin` | string | Origine géographique du café |
| `aromaProfile` | JSON | Profil aromatique (tableau) |
| `readingTimeMinutes` | int | Temps de lecture estimé |
| `coverImage` | string | Nom de fichier de l'image |
| `views` | int | Compteur de vues |
| `metaTitle` | string | Titre SEO |
| `metaDescription` | string | Description SEO |

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
{
  "success": true,
  "average": 4.2,
  "count": 15
}
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
    "category": "Méthodes de Préparation"
  }
]
```

---

## Lancer les tests

```bash
composer install
vendor/bin/phpunit
```

---

## Surcharge des templates

```
templates/bundles/CafeCultureBundle/
├── layout/
│   └── base.html.twig
├── blog/
│   ├── index.html.twig
│   ├── show.html.twig
│   ├── category.html.twig
│   ├── tag.html.twig
│   ├── search.html.twig
│   └── origin.html.twig
└── partials/
    ├── article_card.html.twig
    ├── sidebar.html.twig
    └── pagination.html.twig
```

---

## Contribuer

Les contributions sont les bienvenues ! Merci d'ouvrir une **issue** avant de soumettre une **pull request**.

1. Forkez le dépôt
2. Créez une branche (`git checkout -b feature/ma-fonctionnalite`)
3. Committez vos changements (`git commit -m 'feat: ajouter X'`)
4. Pushez (`git push origin feature/ma-fonctionnalite`)
5. Ouvrez une Pull Request

---

## Licence

Ce bundle est distribué sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus d'informations.

---

## Auteur

**CafeCulture Team** — [hello@cafe-culture.dev](mailto:hello@cafe-culture.dev)

> _"Le café est la seule boisson capable de transformer un développeur en logiciel."_ ☕
