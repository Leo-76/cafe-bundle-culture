# Contribuer à CafeCultureBundle

Merci de votre intérêt pour ce projet !  
Voici les règles et processus pour contribuer efficacement.

---

## Prérequis

- PHP 8.1+
- Composer
- Git

## Démarrage rapide

```bash
# Forker le dépôt sur GitHub, puis :
git clone https://github.com/votre-fork/cafe-culture-bundle.git
cd cafe-culture-bundle
composer install

# Lancer les tests
make test

# Vérifier le style de code
make cs-fix
```

---

## Standards de code

Ce projet suit les standards **Symfony** (PSR-12 + règles Symfony) :

- `declare(strict_types=1)` obligatoire dans tous les fichiers PHP
- Types de retour explicites sur toutes les méthodes
- PHPDoc uniquement si la signature ne suffit pas
- Noms de méthodes en camelCase, classes en PascalCase
- Commentaires en **français** pour la documentation, anglais accepté pour le code

Validation automatique :
```bash
vendor/bin/php-cs-fixer fix src/ tests/ --rules=@Symfony
```

---

## Processus de contribution

1. **Ouvrir une issue** avant de coder une nouvelle fonctionnalité importante
2. **Créer une branche** depuis `main` :
   ```bash
   git checkout -b feat/mon-amelioration
   # ou
   git checkout -b fix/correction-bug
   ```
3. **Coder** en suivant les standards ci-dessus
4. **Écrire des tests** (unitaires et/ou fonctionnels) couvrant les nouvelles fonctionnalités
5. **Vérifier** que tous les tests passent : `make test`
6. **Committer** avec un message conventionnel :
   ```
   feat: ajouter la galerie photos par article
   fix: corriger le calcul du temps de lecture avec HTML
   docs: améliorer le guide d'installation
   test: ajouter tests pour ArticleService::generateSlug
   ```
7. **Ouvrir une Pull Request** vers `main`

---

## Tests

Les nouveaux fichiers PHP dans `src/` doivent avoir leurs tests correspondants dans `tests/`.

```bash
# Tous les tests
vendor/bin/phpunit --testdox

# Un seul fichier de test
vendor/bin/phpunit tests/Entity/ArticleTest.php --testdox

# Avec couverture (nécessite Xdebug)
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text
```

---

## Signaler un bug

Ouvrez une issue GitHub avec :
- La version du bundle (`composer show cafe-culture/blog-bundle`)
- La version de PHP et Symfony
- Les étapes pour reproduire le bug
- Le comportement attendu vs observé
- Les messages d'erreur complets

---

## Code de conduite

Ce projet adhère au [Contributor Covenant](https://www.contributor-covenant.org/fr/).  
Soyez respectueux, inclusif et constructif dans vos échanges.

---

## Licence

En contribuant, vous acceptez que vos contributions soient distribuées sous licence **MIT**.
