# Guide d'installation détaillé — CafeCultureBundle

## Nouvelle application Symfony from scratch

Si vous partez de zéro, voici comment créer une application Symfony avec ce bundle.

### 1. Créer le projet Symfony

```bash
symfony new mon-blog-cafe --version="7.0.*" --webapp
cd mon-blog-cafe
```

### 2. Configurer la base de données

```bash
# .env.local
DATABASE_URL="mysql://root:password@127.0.0.1:3306/cafe_culture?serverVersion=8.0&charset=utf8mb4"
```

```bash
php bin/console doctrine:database:create
```

### 3. Installer le bundle

```bash
composer require cafe-culture/blog-bundle
```

### 4. Configurer le bundle

```yaml
# config/packages/cafe_culture.yaml
cafe_culture:
    posts_per_page: 9
    enable_comments: true
    enable_ratings: true
    sitename: 'Mon Blog Café'
```

### 5. Importer les routes

```yaml
# config/routes/cafe_culture.yaml
cafe_culture:
    resource: '@CafeCultureBundle/config/routes.yaml'
```

### 6. Exécuter la migration et les fixtures

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console assets:install
symfony serve
```

Rendez-vous sur **http://127.0.0.1:8000/blog** 🎉

---

## Intégration dans une application existante

### Vérifier les conflits de routes

Si votre application utilise déjà le préfixe `/blog`, vous pouvez changer le préfixe :

```yaml
# config/routes/cafe_culture.yaml
cafe_culture:
    resource: '@CafeCultureBundle/config/routes.yaml'
    prefix: /cafe  # Changez le préfixe ici
```

### Surcharger la base Twig

Si vous avez déjà un layout de base, héritez-en dans le template du bundle :

```twig
{# templates/bundles/CafeCultureBundle/layout/base.html.twig #}
{% extends 'base.html.twig' %}  {# Votre propre base #}

{% block body %}
    {{ parent() }}
{% endblock %}
```

### Utiliser votre propre système d'upload

Le bundle utilise par défaut un répertoire d'upload simple.  
Pour intégrer VichUploader ou LiipImagine, surchargez `Article::$coverImage`.

---

## Personnalisation du thème CSS

Le CSS est dans `public/bundles/cafeculturebundle/css/main.css`.  
Pour personnaliser les couleurs sans modifier le fichier source, surchargez les variables CSS dans votre propre feuille de style :

```css
/* assets/styles/cafe-overrides.css */
:root {
    --cafe-brown: #4a2c17;   /* Couleur principale */
    --cafe-gold:  #b8860b;   /* Couleur d'accent */
    --font-display: 'Merriweather', serif;  /* Police de titres */
}
```

---

## Créer un article via le code

```php
use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Category;

$category = $entityManager->getRepository(Category::class)->findOneBy(['slug' => 'recettes']);

$article = (new Article())
    ->setTitle('Mon Espresso Maison')
    ->setSlug('mon-espresso-maison')
    ->setContent('Contenu de l\'article...')
    ->setCategory($category)
    ->setCoffeeOrigin('Colombie')
    ->setAromaProfile(['chocolaté', 'caramel', 'noisette'])
    ->setReadingTimeMinutes(5)
    ->setAuthorName('Jean Barista')
    ->setFeatured(true)
    ->publish();

$entityManager->persist($article);
$entityManager->flush();
```

---

## Commandes utiles

```bash
# Lister toutes les routes du bundle
php bin/console debug:router | grep cafe_culture

# Vérifier le mapping Doctrine
php bin/console doctrine:mapping:info

# Mettre à jour le schéma (développement)
php bin/console doctrine:schema:update --force

# Vider le cache
php bin/console cache:clear

# Lancer les tests
vendor/bin/phpunit --testdox
```

---

## FAQ

**Q : Les images ne s'affichent pas.**  
R : Vérifiez que `php bin/console assets:install` a bien été exécuté et que le répertoire `public/bundles/cafeculturebundle/` existe.

**Q : Les commentaires ne sont pas visibles après soumission.**  
R : Les commentaires nécessitent une approbation manuelle. Mettez `approved = true` en base de données ou créez une interface d'administration.

**Q : Comment modifier le nombre d'articles par page ?**  
R : Changez `posts_per_page` dans `config/packages/cafe_culture.yaml`.

**Q : Comment ajouter une catégorie personnalisée ?**  
R : Utilisez directement l'entité `Category` via Doctrine ou ajoutez-la dans vos fixtures.
