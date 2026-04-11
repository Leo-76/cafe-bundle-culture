# Politique de sécurité — CafeCultureBundle

## Versions supportées

| Version | Supportée |
|---------|-----------|
| 1.x     | ✅ Oui    |

## Signaler une vulnérabilité

**Ne pas ouvrir d'issue publique pour les failles de sécurité.**

Envoyez un e-mail à : **security@cafe-culture.dev**

Incluez :
- Description de la vulnérabilité
- Étapes pour la reproduire
- Impact potentiel
- Suggestion de correctif (optionnel)

Vous recevrez une réponse sous **72 heures**.  
Les vulnérabilités confirmées feront l'objet d'un correctif et d'un avis de sécurité public.

## Mesures de sécurité en place

- **CSRF** : tous les formulaires POST utilisent les tokens CSRF Symfony
- **XSS** : Twig échappe automatiquement toutes les variables
- **Injection SQL** : requêtes Doctrine paramétrées uniquement
- **Upload** : validation MIME type + taille côté serveur
- **Accès admin** : `#[IsGranted('ROLE_ADMIN')]` sur toutes les routes back-office
- **Rate limiting** : une notation par IP par article (contrainte unique BDD)
- **Commentaires** : modération obligatoire avant publication
