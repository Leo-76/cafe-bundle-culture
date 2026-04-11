# Guide de publication sur Packagist

## Pourquoi l'erreur "minimum-stability (stable)"

Packagist ne connait pas les versions depuis le fichier `composer.json`.
Il les detecte **uniquement depuis les tags Git** du depot GitHub/GitLab.

Sans tag Git, Packagist classe le package comme `dev-main` (instabilite dev),
ce que Composer refuse d'installer par defaut.

---

## Solution : creer un tag Git stable

```bash
# 1. Se placer dans le depot du bundle
cd cafe-culture-bundle/

# 2. S'assurer que tout est committe
git add .
git commit -m "chore: version 1.1.0 — support Symfony 8"

# 3. Creer le tag de version (OBLIGATOIRE pour Packagist)
git tag v1.1.0

# 4. Pousser le code ET le tag sur GitHub
git push origin main
git push origin v1.1.0

# 5. Attendre ~1 minute que Packagist indexe le tag
# Puis tester :
composer require cafe-culture/blog-bundle
```

---

## Regles de nommage des tags

Packagist reconnait ces formats comme versions **stables** :

| Tag Git     | Version Composer | Stabilite |
|-------------|-----------------|-----------|
| `v1.0.0`    | `1.0.0`         | stable    |
| `v1.1.0`    | `1.1.0`         | stable    |
| `v2.0.0`    | `2.0.0`         | stable    |
| `v1.0.0-RC1`| `1.0.0-RC1`     | RC        |
| `v1.0.0-beta1`| `1.0.0-beta1` | beta      |

**Ne jamais mettre le champ `"version"` dans `composer.json`** — Packagist l'ignore
et Composer peut s'y perdre. La version vient toujours du tag Git.

---

## Workflow complet de publication

```bash
# Premiere publication
git init
git add .
git commit -m "feat: initial release"
git remote add origin git@github.com:cafe-culture/blog-bundle.git
git push -u origin main
git tag v1.1.0
git push origin v1.1.0

# Aller sur https://packagist.org/packages/submit
# Entrer l'URL du depot GitHub -> Submit
# Packagist detecte automatiquement les tags

# Pour les versions suivantes
git tag v1.2.0
git push origin v1.2.0
# Packagist se met a jour automatiquement via webhook GitHub
```

---

## Configurer le webhook GitHub -> Packagist

Pour que Packagist soit notifie automatiquement a chaque push/tag :

1. Aller dans votre depot GitHub > **Settings > Webhooks > Add webhook**
2. Payload URL : `https://packagist.org/api/github?username=VOTRE_USERNAME`
3. Content type : `application/json`
4. Secret : votre token API Packagist (depuis https://packagist.org/profile/)
5. Events : **Just the push event**

Ou plus simplement via les **GitHub Apps** :
1. https://packagist.org/profile/ > "Connect with GitHub"
2. Autoriser l'acces au depot
3. Packagist se synchronise automatiquement

---

## Verifier que le package est bien indexe

```bash
# Voir toutes les versions detectees par Packagist
composer show cafe-culture/blog-bundle --all

# Installer une version precise
composer require cafe-culture/blog-bundle:^1.1

# Forcer l'installation depuis la branche main (dev)
composer require cafe-culture/blog-bundle:dev-main
```
