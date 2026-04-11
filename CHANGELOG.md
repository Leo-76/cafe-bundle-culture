# Changelog — cafe-culture/blog-bundle

Toutes les modifications notables de ce projet sont documentées dans ce fichier.
Format basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

---

## [1.0.0] — 2024-01-01

### Ajouté
- Entités Doctrine : `Article`, `Category`, `Tag`, `Comment`, `Rating`
- Champs spécialisés café : `coffeeOrigin`, `aromaProfile`, `readingTimeMinutes`
- `BlogController` avec routes : index, show, category, tag, search, origin
- `ApiController` REST : notation AJAX, recherche, articles récents
- Extension Twig : filtres `reading_time`, `excerpt`, `stars` ; fonctions `coffee_flag`, `aroma_badge`
- Globals Twig automatiques : catégories, articles récents, tags populaires, articles vus
- `SlugSubscriber` : génération automatique des slugs via `prePersist`
- `CafeFixtures` : 6 articles de contenu complet, 6 catégories, 15 tags
- Migration SQL initiale : 6 tables avec index et clés étrangères
- Thème CSS complet (600+ lignes) : navbar sticky, hero, grille d'articles, sidebar, footer
- JavaScript : notation AJAX, autocomplete de recherche, navbar mobile
- Templates Twig : `base`, `index`, `show`, `category`, `tag`, `search`, `origin`, partials
- Configuration `DependencyInjection` avec paramètres personnalisables
- Suite de tests PHPUnit : 20+ tests unitaires et fonctionnels
- Support Symfony 6.4 et 7.x
- Support PHP 8.1+

---

## À venir — [1.1.0]

- [ ] Administration EasyAdmin intégrée
- [ ] Sitemap XML automatique
- [ ] Flux RSS/Atom
- [ ] Internationalisation (i18n) anglais/espagnol
- [ ] Cache HTTP avec ETags
- [ ] Newsletter (intégration Symfony Mailer)
- [ ] Galerie photos par article
- [ ] Carte interactive des origines géographiques

---

## [1.1.0] — 2024-06-01

### Modifie
- Support Symfony **8.0** ajoute (`^6.4|^7.0|^8.0`) — resout l'erreur de compatibilite Packagist
- PHP minimum bump a **8.2** (requis par Symfony 8)
- PHPUnit passe a `^11.0` (compatible PHP 8.2+)
- `knplabs/knp-paginator-bundle` minimum monte a `^6.3` (supporte Symfony 8)
- `liip/imagine-bundle`, `vich/uploader-bundle`, `stof/doctrine-extensions-bundle` passes en **suggest** (optionnels) pour eviter les conflits de versions

### Corrections
- Erreur Composer `conflicts with your root composer.json require (8.0.*)` resolue
- CI GitHub Actions mis a jour : matrice PHP 8.2 / 8.3 / 8.4 x Symfony 6.4 / 7.2 / 8.0
