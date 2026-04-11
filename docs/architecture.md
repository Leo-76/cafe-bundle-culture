# Architecture — CafeCultureBundle

## Vue d'ensemble

```
cafe-culture/blog-bundle
├── src/
│   ├── CafeCultureBundle.php           Point d'entrée du bundle
│   ├── Command/                        Commandes console Symfony
│   │   ├── ArticleStatsCommand.php     cafe:articles:stats
│   │   └── PublishDraftCommand.php     cafe:articles:publish
│   ├── Controller/                     Contrôleurs HTTP
│   │   ├── BlogController.php          Routes publiques du blog
│   │   ├── AdminController.php         Back-office (ROLE_ADMIN)
│   │   ├── ApiController.php           API REST légère (JSON)
│   │   └── SitemapController.php       Sitemap XML + flux RSS
│   ├── DependencyInjection/
│   │   ├── CafeCultureExtension.php    Chargeur de configuration
│   │   └── Configuration.php          Arbre de configuration Symfony
│   ├── DataFixtures/
│   │   └── CafeFixtures.php            Données de démonstration
│   ├── Entity/
│   │   ├── Article.php                 Entité principale
│   │   ├── Category.php                Catégories thématiques
│   │   ├── Tag.php                     Tags libres
│   │   ├── Comment.php                 Commentaires (avec modération)
│   │   └── Rating.php                  Notes par étoiles (1 par IP)
│   ├── EventSubscriber/
│   │   └── SlugSubscriber.php          Auto-génération des slugs
│   ├── Form/
│   │   ├── ArticleType.php             Formulaire article complet
│   │   ├── CategoryType.php            Formulaire catégorie
│   │   └── CommentType.php             Formulaire commentaire public
│   ├── Listener/
│   │   ├── ArticleListener.php         prePersist/preUpdate article
│   │   └── ViewCountListener.php       Comptage vues (cookie anti-dupe)
│   ├── Repository/
│   │   ├── ArticleRepository.php       Requêtes DQL optimisées
│   │   ├── CategoryRepository.php
│   │   ├── TagRepository.php
│   │   ├── CommentRepository.php
│   │   └── RatingRepository.php
│   ├── Service/
│   │   ├── ArticleService.php          Logique métier articles
│   │   └── ImageUploadService.php      Upload et gestion fichiers
│   └── Twig/
│       └── CafeCultureExtension.php    Extension Twig (filtres + globals)
├── templates/
│   ├── admin/                          Templates back-office
│   │   ├── dashboard.html.twig
│   │   ├── articles/{index,form}.html.twig
│   │   ├── categories/{index,form}.html.twig
│   │   ├── comments/index.html.twig
│   │   └── partials/sidebar.html.twig
│   ├── blog/                           Templates blog public
│   │   ├── index.html.twig             Accueil avec hero + grille
│   │   ├── show.html.twig              Article + commentaires + rating
│   │   ├── category.html.twig
│   │   ├── tag.html.twig
│   │   ├── search.html.twig
│   │   └── origin.html.twig
│   ├── layout/
│   │   └── base.html.twig             Layout de base (navbar + footer)
│   ├── partials/
│   │   ├── article_card.html.twig
│   │   ├── sidebar.html.twig
│   │   └── pagination.html.twig
│   └── sitemap/
│       ├── sitemap.xml.twig
│       └── rss.xml.twig
├── public/bundles/cafeculturebundle/
│   ├── css/
│   │   ├── main.css                   Thème blog (600+ lignes)
│   │   └── admin.css                  Thème back-office
│   └── js/
│       ├── app.js                     JS blog public
│       └── admin.js                   JS back-office
├── config/
│   ├── services.yaml                  Définitions de services
│   ├── routes.yaml                    Import des routes
│   └── packages/cafe_culture.yaml    Configuration par défaut
├── migrations/
│   └── Version20240101000001.php     Migration SQL initiale
├── tests/
│   ├── App/TestKernel.php            Kernel de test
│   ├── Controller/BlogControllerTest.php
│   └── Entity/
│       ├── ArticleTest.php
│       ├── ArticleServiceTest.php
│       ├── CategoryTest.php
│       ├── SlugSubscriberTest.php
│       └── TwigExtensionTest.php
└── docs/
    ├── installation.md
    └── architecture.md               (ce fichier)
```

---

## Flux de données

### Publication d'un article

```
ArticleType (form)
    └─► AdminController::articleNew()
            ├─► ArticleService::prepareForPersist()
            │       ├─► generateSlug()      → slug unique en BDD
            │       ├─► computeReadingTime() → calcul depuis word count
            │       ├─► ensureExcerpt()     → tronqué depuis content
            │       └─► ensureSeoFields()   → meta title + description
            ├─► ImageUploadService::upload() → fichier dans /uploads/
            └─► EntityManager::persist() + flush()
```

### Affichage d'un article

```
GET /blog/article/{slug}
    └─► BlogController::show()
            ├─► ArticleRepository::findOneBy(['slug', 'status'])
            ├─► ViewCountListener (cookie anti-dupe, +1 vue)
            ├─► CommentType (formulaire vide)
            └─► Render show.html.twig
                    ├─► @CafeCulture/layout/base.html.twig
                    │       └─► CafeCultureExtension::getGlobals()
                    │               ├─► cafe_categories (nav + footer)
                    │               ├─► cafe_recent (sidebar)
                    │               ├─► cafe_popular_tags (sidebar)
                    │               └─► cafe_most_viewed (sidebar)
                    └─► stars|filter, coffee_flag(), aroma_badge()
```

### Notation AJAX

```
POST /api/cafe/rate/{id}  {score: 4}
    └─► ApiController::rate()
            ├─► RatingRepository::hasAlreadyRated() [unicité IP]
            ├─► new Rating() → persist + flush
            └─► JSON {success, average, count}
```

---

## Schéma de base de données

```
cafe_category ──────────────────────────────────────────────────
  id | name | slug | description | color | icon | position

cafe_article ────────────────────────────────────────────────────
  id | category_id* | title | slug | content | excerpt
  cover_image | status | featured | views | reading_time_minutes
  coffee_origin | aroma_profile(JSON) | author_name
  meta_title | meta_description | created_at | updated_at | published_at

cafe_tag ────────────────────────────────────────────────────────
  id | name | slug

cafe_article_tag  [pivot N:N] ───────────────────────────────────
  article_id* | tag_id*

cafe_comment ────────────────────────────────────────────────────
  id | article_id* | author_name | author_email
  content | approved | created_at

cafe_rating  [unicité article_id + ip_address] ──────────────────
  id | article_id* | score(1-5) | ip_address | created_at
```

---

## Extension Twig

### Globals disponibles dans tous les templates

| Variable | Type | Source |
|----------|------|--------|
| `cafe_categories` | `Category[]` | `CategoryRepository::findAllOrdered()` |
| `cafe_recent` | `Article[]` | `ArticleRepository::findRecent(5)` |
| `cafe_popular_tags` | `Tag[]` | `TagRepository::findPopular(15)` |
| `cafe_most_viewed` | `Article[]` | `ArticleRepository::findMostViewed(5)` |

### Filtres

| Filtre | Exemple | Résultat |
|--------|---------|----------|
| `reading_time` | `{{ content\|reading_time }}` | `"7 min de lecture"` |
| `excerpt` | `{{ content\|excerpt(160) }}` | Texte tronqué proprement |
| `stars` | `{{ 4.5\|stars }}` | HTML ★★★★½ |

### Fonctions

| Fonction | Exemple | Résultat |
|----------|---------|---------|
| `coffee_flag` | `{{ coffee_flag('Éthiopie') }}` | `<span>🇪🇹</span>` |
| `aroma_badge` | `{{ aroma_badge('chocolaté') }}` | Badge coloré HTML |

---

## Sécurité

- Les routes `/admin/cafe/*` sont protégées par `#[IsGranted('ROLE_ADMIN')]`
- Les tokens CSRF protègent toutes les actions POST destructives (suppression, publication)
- Le comptage de vues utilise un cookie `HttpOnly` + `SameSite=Lax` pour éviter les abus
- Les notations sont limitées à une par adresse IP par article (index unique en BDD)
- Les commentaires passent obligatoirement par une modération manuelle avant publication
