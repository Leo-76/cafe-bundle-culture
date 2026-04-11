# Makefile — CafeCultureBundle
# Usage : make <cible>

.PHONY: install test cs-fix lint analyse clean help

##@ 🛠️  Développement

install: ## Installer les dépendances Composer
	composer install

test: ## Lancer la suite de tests PHPUnit
	vendor/bin/phpunit --testdox

test-coverage: ## Tests avec rapport de couverture HTML
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html var/coverage

##@ 🎨  Qualité de code

cs-fix: ## Corriger le style de code (PHP-CS-Fixer)
	vendor/bin/php-cs-fixer fix src/ tests/ --rules=@Symfony

lint: ## Vérifier la syntaxe PHP
	find src tests -name "*.php" | xargs php -l

analyse: ## Analyse statique (PHPStan niveau 8)
	vendor/bin/phpstan analyse src/ --level=8

##@ 🧹  Nettoyage

clean: ## Supprimer les fichiers temporaires
	rm -rf var/ .phpunit.result.cache

##@ 📖  Aide

help: ## Afficher cette aide
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<cible>\033[0m\n"} /^[a-zA-Z_0-9-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help
