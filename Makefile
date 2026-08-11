HOST_GROUP_ID = $(shell id -g)
HOST_USER = ${USER}
HOST_UID = $(shell id -u)

export HOST_UID
export HOST_USER
export HOST_GROUP_ID

DOCKER_COMPOSE_DEV = docker compose

install:
	$(DOCKER_COMPOSE_DEV) build
	$(MAKE) composer-install
	$(MAKE) assets-install
	$(MAKE) start

composer-install:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'php -d memory_limit=4G bin/composer install'

composer-update:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'php -d memory_limit=4G bin/composer update -W'

start:
	$(DOCKER_COMPOSE_DEV) up -d

start-verbose:
	$(DOCKER_COMPOSE_DEV) up

stop:
	$(DOCKER_COMPOSE_DEV) down

connect:
	$(DOCKER_COMPOSE_DEV) exec php bash

clear:
	$(DOCKER_COMPOSE_DEV) exec php php ./bin/console cache:clear

assets-install:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'php -d memory_limit=4G ./bin/console importmap:install'

assets-compile:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'php -d memory_limit=4G ./bin/console asset-map:compile'

test:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'php ./bin/phpunit'

phpstan:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'php -d memory_limit=1G ./vendor/bin/phpstan analyse'

cs:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'vendor/bin/phpcs'

csfix:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'vendor/bin/phpcbf'

migrate:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'php ./bin/console doctrine:migrations:migrate --no-interaction'

fixtures:
	$(DOCKER_COMPOSE_DEV) run --rm php bash -ci 'php ./bin/console doctrine:fixtures:load --no-interaction'

logs:
	$(DOCKER_COMPOSE_DEV) logs -f

restart:
	$(DOCKER_COMPOSE_DEV) restart

destroy:
	$(DOCKER_COMPOSE_DEV) down -v

.PHONY: install composer-install composer-update start start-verbose stop connect clear assets-install assets-compile test phpstan cs csfix migrate fixtures logs restart destroy