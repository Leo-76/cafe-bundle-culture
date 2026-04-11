# Publier cafe-culture/blog-bundle sur Packagist

## Pourquoi l'erreur "minimum-stability"

Packagist ne connait pas encore ce package.
Il faut le publier en suivant les etapes ci-dessous.

---

## Etape 1 — Creer le depot GitHub

```bash
# Dans le dossier du bundle
cd cafe-culture-bundle

git init
git add .
git commit -m "feat: initial release v1.1.0"

# Creer le depot sur GitHub (via gh CLI ou interface web)
gh repo create cafe-culture/blog-bundle --public --source=. --push
# ou manuellement :
git remote add origin https://github.com/cafe-culture/blog-bundle.git
git push -u origin main
```

## Etape 2 — Tagger une version stable

Packagist detecte les versions via les **tags Git**.
Sans tag, Composer considere tout comme "dev" (instable).

```bash
git tag v1.1.0
git push origin v1.1.0
```

## Etape 3 — Soumettre sur Packagist

1. Aller sur https://packagist.org/packages/submit
2. Coller l'URL du depot : `https://github.com/cafe-culture/blog-bundle`
3. Cliquer **Check** puis **Submit**

Packagist lit automatiquement les tags Git comme versions stables.

## Etape 4 — Activer le webhook GitHub (mises a jour auto)

Dans les settings du depot GitHub :
Settings > Webhooks > Add webhook
- Payload URL : `https://packagist.org/api/github?username=cafe-culture`
- Content type : `application/json`
- Secret : votre token Packagist (Profile > API Token)
- Events : **Just the push event**

---

## En attendant la publication — Test local

### Option A : path repository (le plus simple)

Dans le `composer.json` de votre projet Symfony :

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../cafe-culture-bundle",
            "options": {
                "symlink": true
            }
        }
    ],
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

```bash
composer require cafe-culture/blog-bundle:@dev
```

### Option B : VCS repository (depuis GitHub sans passer par Packagist)

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/cafe-culture/blog-bundle"
        }
    ]
}
```

```bash
composer require cafe-culture/blog-bundle:dev-main
# ou apres avoir cree le tag :
composer require cafe-culture/blog-bundle:^1.1
```

### Option C : Satis (miroir Packagist prive)

Pour une equipe ou un usage interne sans publication publique.

---

## Checklist avant publication Packagist

- [ ] `composer.json` valide (`composer validate`)
- [ ] Pas de champ `"version"` hardcode (Packagist le lit depuis les tags Git)
- [ ] Tag Git `v1.1.0` pousse sur GitHub
- [ ] `README.md` presente avec badges et instructions d'installation
- [ ] `LICENSE` present
- [ ] Tests passent (`vendor/bin/phpunit`)
