# Symfony7-php8.4-docker

Starter kit pour applications Symfony 8.1 / PHP 8.5 avec Docker.

## Stack technique

- PHP 8.5 (FPM)
- Symfony 8.1
- PostgreSQL 18
- Nginx
- Mailpit (interface web : http://localhost:1180)

## Prérequis

- Docker & Docker Compose
- Make

## Installation

```bash
make install
```

Lance le build des images Docker, installe les dépendances Composer et les assets, puis démarre les containers.

## Commandes disponibles

| Commande | Description |
|---|---|
| `make install` | Build, install et démarrage complet |
| `make start` | Démarrage des containers |
| `make stop` | Arrêt des containers |
| `make connect` | Shell dans le container PHP |
| `make clear` | Vide le cache Symfony |
| `make composer-update` | Mise à jour des vendors PHP |
| `make assets-install` | Installe les assets via importmap |
| `make assets-compile` | Compile les assets pour la production |

## URLs

| Service | URL |
|---|---|
| Application (dev) | http://localhost:8081 |
| Mailpit | http://localhost:1180 |
| PostgreSQL (host) | localhost:5532 |

## Développement

### Symfony console

```bash
make connect
php bin/console <commande>
```

### Migrations

```bash
php bin/console doctrine:migrations:migrate
php bin/console make:migration
```

### Fixtures

```bash
php bin/console doctrine:fixtures:load
```

### Tests

```bash
php bin/phpunit                              # Tous les tests
php bin/phpunit tests/path/to/SomeTest.php  # Un fichier
php bin/phpunit --filter testMethodName     # Une méthode
```

### Qualité de code

```bash
vendor/bin/phpstan analyse   # Analyse statique (niveau 6)
vendor/bin/phpcs             # Vérification PSR-12
vendor/bin/phpcbf            # Correction automatique PSR-12
```

## Variables d'environnement

Les variables sont définies dans `.env`. Les principales :

| Variable | Valeur par défaut |
|---|---|
| `APP_PORT` | `8081` |
| `DATABASE_HOST` | `database` |
| `DATABASE_PORT` | `5432` |
| `DATABASE_NAME` | `symfony6-docker` |
| `MAILER_DSN` | `smtp://mailer:1025` |
