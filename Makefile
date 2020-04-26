DOCKER 		   = @docker
DOCKER_COMPOSE = @docker-compose
PHP            = $(DOCKER_COMPOSE) run --rm php

.DEFAULT_GOAL := help
.PHONY: boot up down vendor tests

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Project
##---------------------------------------------------------------------------

boot: ## Launch the project
boot: up vendor

up: ## Up the containers
up:
	$(DOCKER_COMPOSE) up -d --remove-orphans

down: ## Down the containers
down:
	$(DOCKER_COMPOSE) down

vendor: ## Install the dependencies
vendor:
	$(PHP) composer install

##
## Tools
##---------------------------------------------------------------------------

php-cs-fixer: ## Run PHP-CS-FIXER against a specific DIRECTORY
php-cs-fixer:
	$(PHP) vendor/bin/php-cs-fixer fix $(DIRECTORY)

##
## Tests
##---------------------------------------------------------------------------

tests: ## Launch the PHPUnit tests
tests:
	$(PHP) vendor/bin/phpunit tests

infection: ## Launch Infection
infection:
	$(PHP) vendor/bin/infection
