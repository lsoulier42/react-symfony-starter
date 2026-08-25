<h1 align="center">Starter Kit — Symfony 8.1 · PHP 8.5 · Docker</h1>

<p align="center">
  <a href="https://www.php.net/releases/8.5/en.php"><img alt="PHP 8.5" src="https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php&logoColor=white" /></a>
  <a href="https://symfony.com/releases/8.1"><img alt="Symfony 8.1" src="https://img.shields.io/badge/Symfony-8.1-000000?style=flat-square&logo=symfony&logoColor=white" /></a>
  <a href="https://www.postgresql.org/"><img alt="PostgreSQL 18" src="https://img.shields.io/badge/PostgreSQL-18-336791?style=flat-square&logo=postgresql&logoColor=white" /></a>
  <a href="https://www.docker.com/"><img alt="Docker" src="https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white" /></a>
  <a href="LICENSE"><img alt="License: MIT" src="https://img.shields.io/badge/License-MIT-2ea44f?style=flat-square" /></a>
</p>

<p align="center">
  Containerized <strong>Symfony 8.1</strong> application skeleton (PHP 8.5 FPM), ready for production,
  with French locale by default, strict quality gates (PSR‑12 + PHPStan level 6)
  and a set of opinionated base classes to get started quickly.
</p>

---

## 📚 Table of Contents

- [Overview](#-overview)
- [Key features](#-key-features)
- [Tech stack](#-tech-stack)
- [Prerequisites](#-prerequisites)
- [Quick start](#-quick-start)
- [Configuration](#-configuration)
- [Commands (Makefile)](#-commands-makefile)
- [Services & URLs](#-services--urls)
- [Project structure](#-project-structure)
- [Architecture & conventions](#-architecture--conventions)
- [Database & migrations](#-database--migrations)
- [Messenger & async emails](#-messenger--async-emails)
- [Tests](#-tests)
- [Code quality](#-code-quality)
- [Docker](#-docker)
- [Contribution](#-contribution)
- [License](#-license)
- [Additional documentation](#-additional-documentation)

---

## 📖 Overview

This repository is a **starter kit** (starting point) for building a modern Symfony web application, fully running in Docker containers. It provides:

- a reproducible development environment (PHP 8.5 FPM, Nginx, PostgreSQL 18, Mailpit) ;
- a proven Symfony 8.1 setup (Doctrine ORM 3, Messenger, Mailer, AssetMapper, Stimulus, Twig, Security, Validator, Translator) ;
- **base classes** (`AbstractBaseController`, `AbstractApiController`, `AbstractEntity`, `AbstractRepository`, `AbstractFixtures`) and **utilities** (`Helper/`, `Dto/`, `Trait/`, `Story/`) to speed up development ;
- built-in code quality gates (PHPStan level 6, phpcs PSR‑12, PHPUnit 12).

> The project ships with a home page (`HomepageController`) plus a complete `User` example: authentication (login form, admin area) and a representative JSON CRUD API. The architecture stays "empty but structured" — build your own business entities on top.

---

## ✨ Key features

- **Full containerization**: PHP (FPM), Nginx, PostgreSQL and Mailpit orchestrated via `docker compose`.
- **Supervised Messenger workers**: asynchronous consumption of messages (emails, tasks) via `supervisord` in the PHP container.
- **Frictionless local emails**: Mailpit captures all outgoing emails and provides a web-based debugging interface.
- **Ready-to-use pagination**: Pagerfanta integration via `QueryBuilderHelper::findAllPaginated()` + `PaginationDto`.
- **"Timestamped" entities & UUID**: `AbstractEntity` + `Trait\Timestampable` (automatic management of `createdAt`/`updatedAt` and a `Uuid` v4).
- **Authentication & example entity**: `User` with login form, admin area and a JSON CRUD API built on Symfony's native APIs (`MapRequestPayload`, `MapQueryString`, Serializer groups).
- **Business helpers**: slug generation (`SluggerHelper`), French date formatting (`DateTimeHelper`), QueryBuilder builders (`QueryBuilderHelper`).
- **French locale by default**: `config/services.yaml` (`locale: fr`) and translation catalogs in `translations/`.
- **Strict code quality**: PHPStan level 6, PHP_CodeSniffer (PSR‑12), PHPUnit 12.

---

## 🧱 Tech stack

| Area | Technology | Version |
|---|---|---|
| Language | PHP (FPM) | **8.5** |
| Framework | Symfony | **8.1** |
| Database | PostgreSQL | **18** (`postgres:18.2-alpine`) |
| Web server | Nginx | **1.29** (`1.29.5-alpine`) |
| Mail (dev) | Mailpit | `axllent/mailpit` |
| ORM | Doctrine ORM / DBAL | **3.6** / **4.4** |
| Pagination | Pagerfanta | **4.x** |
| Frontend | AssetMapper + Stimulus + Bootstrap | Symfony 8.1 assets |
| Quality | PHPStan / PHP_CodeSniffer / PHPUnit | **2.x** / **3.7** / **12** |

---

## 📋 Prerequisites

- **Docker** ≥ 24 and **Docker Compose** (v2, included in the Docker CLI).
- **Make** (the `make` utility) to run the `Makefile` recipes.
- **Git** to clone the repository.
- No local PHP/Composer/PostgreSQL installation is required: everything runs in the containers.

> ℹ️ On Linux, make sure your user belongs to the `docker` group (otherwise prefix the commands with `sudo`).

---

## 🚀 Quick start

```bash
# 1. Clone the repository
git clone git@github.com:lsoulier42/symfony-docker-starter.git
cd symfony-docker-starter

# 2. Build the images, install the dependencies and start
make install

# 3. Open the application
#    → http://localhost:8081
```

`make install` automatically runs: `docker compose build`, `composer install`, `importmap:install` and `docker compose up -d`.

To initialize the database (once the containers are running):

```bash
make connect                      # shell into the PHP container
php bin/console doctrine:migrations:migrate
```

---

## ⚙️ Configuration

All per-environment configuration is centralized in **`.env`** (committed default values) and can be overridden locally by **`.env.local`** (not versioned). The test environment uses **`.env.test`**.

| Variable | Default | Description |
|---|---|---|
| `APP_ENV` | `dev` | Symfony environment (`dev` / `prod` / `test`). |
| `APP_SECRET` | `6bd8b04b…` | Application secret key. |
| `APP_VERSION` | `0.1.0` | Application version (exposed via the `app_version` parameter). |
| `APP_PORT` | `8081` | Host port exposed by Nginx. |
| `DATABASE_HOST` | `database` | PostgreSQL service name (Docker network). |
| `DATABASE_PORT` | `5432` | PostgreSQL internal port. |
| `DATABASE_USER` | `root` | PostgreSQL user. |
| `DATABASE_PASSWORD` | `password` | PostgreSQL password. |
| `DATABASE_NAME` | `symfony` | PostgreSQL database name. |
| `DATABASE_URL` | `postgresql://…` | Doctrine DSN built from the variables above. |
| `MAILER_DSN` | `smtp://mailer:1025` | Mailer transport (Mailpit). |
| `MESSENGER_TRANSPORT_DSN` | `doctrine://default?auto_setup=0` | Messenger async transport (Doctrine DBAL). |

> The PostgreSQL database name is `symfony` (consistent between `.env` and the code).

---

## 🛠 Commands (Makefile)

All common operations go through `make` (Composer/PHP commands run **inside** the PHP container).

| Command | Description |
|---|---|
| `make install` | Build images + `composer install` + assets + full start. |
| `make start` | Starts the containers in the background. |
| `make start-verbose` | Starts the containers and shows live logs. |
| `make stop` | Stops and removes the containers (`docker compose down`). |
| `make connect` | Opens a Bash shell in the PHP container. |
| `make clear` | Clears the Symfony cache (`cache:clear`). |
| `make composer-install` | `composer install` (high memory limit) in the container. |
| `make composer-update` | `composer update -W` in the container. |
| `make assets-install` | Installs frontend dependencies via `importmap:install`. |
| `make assets-compile` | Compiles assets for production (`asset-map:compile`). |

---

## 🌐 Services & URLs

| Service | URL / host | Notes |
|---|---|---|
| Application (dev) | `http://localhost:8081` | Served by Nginx + PHP‑FPM. |
| Mailpit (web interface) | `http://localhost:1181` | Dev email viewer. |
| PostgreSQL (host) | `localhost:5532` | Port mapped from the `database` container. |
| Mailpit SMTP (host) | `localhost:1126` | Mapped SMTP port (internal `1025`). |

---

## 🗂 Project structure

```
.
├── bin/                      # Executables (console, composer, phpunit)
├── config/                  # Symfony configuration (packages/, services.yaml, routes…)
├── docker/
│   ├── db/                  # PostgreSQL data volume
│   ├── nginx/default.conf   # Nginx configuration (PHP‑FPM, front controller)
│   └── php/                 # PHP Dockerfile, supervisord (Messenger workers), entrypoint
├── migrations/              # Doctrine migrations
├── public/                  # Document root (index.php, favicon…)
├── src/
│   ├── Controller/          # Controllers (base controllers, auth, admin, User API)
│   ├── Entity/              # Doctrine entities (AbstractEntity, User)
│   ├── Repository/          # Repositories (AbstractRepository, UserRepository)
│   ├── Dto/                 # PaginationDto, UserPayload
│   ├── Helper/              # DateTimeHelper, QueryBuilderHelper, SluggerHelper
│   ├── Trait/               # SlugTrait, SoftDeleteTrait, Timestampable
│   ├── DataFixtures/        # AbstractFixtures, UserFixtures
│   ├── Story/               # Foundry stories (AppStory)
│   └── Kernel.php
├── templates/               # Twig templates (base, layout, homepage)
├── assets/                  # Frontend sources (JS/CSS, importmap, Stimulus)
├── tests/                   # PHPUnit tests (bootstrap.php)
├── translations/            # Translation catalogs (messages+intl-icu.fr.yaml)
├── docker-compose.yaml
├── Makefile
├── composer.json
└── phpstan.dist.neon / phpcs.xml.dist / phpunit.xml.dist
```

---

## 🏗 Architecture & conventions

The goal of this starter kit is to **reduce boilerplate**: extend the base classes rather than reimplementing everything.

### Base classes to extend

| Class | Role |
|---|---|
| `App\Controller\AbstractBaseController` | Adds `createPaginationDto()`, `addSuccessMessage()`, `addWarningMessage()`, `addErrorMessage()`. |
| `App\Controller\AbstractApiController` | Base for JSON REST APIs: `jsonResponse()`, `created()`, `noContent()`, `unprocessable()`. |
| `App\Entity\AbstractEntity` | Provides `id` (SEQUENCE), `uuid` (Uuid v4) and the `Timestampable` trait. |
| `App\Repository\AbstractRepository` | Persistence helpers: `createOrUpdate()`, `remove()`, `findOneByUuid()`, `paginate()`. |
| `App\DataFixtures\AbstractFixtures` | Initializes a `Faker` generator (`fr_FR`) for fixtures. |

### Helpers & DTO

- `App\Helper\DateTimeHelper` — `formatMonthYearFrench()` and `FRENCH_MONTHS` constants.
- `App\Helper\SluggerHelper` — `slugify()` wrapper around Symfony's `AsciiSlugger` for manual slug generation.
- `App\Helper\QueryBuilderHelper` — static Doctrine QueryBuilder builders: `addFieldLike()`, `addFieldAndWhere()`, `addTableJoin()`, `addPeriodWhere()`, `addRandomElements()`, `getCollectionFromQueryBuilder()`, `findAllPaginated()`.
- `App\Dto\PaginationDto` — validated pagination payload (`page`, `limit`), compatible with Pagerfanta.
- `App\Dto\UserPayload` — validated input DTO (`email`, `plainPassword`, `roles`) for the User API.

### Autowiring & service registration

`config/services.yaml` enables **autowiring** and **autoconfiguration** for the `App\` prefix. Everything under `src/` is registered automatically **except**:

- `src/Entity/` (Doctrine entities, intentionally excluded) ;
- `src/Kernel.php` ;
- `src/DependencyInjection/`.

### Locale & translations

The default locale is **`fr`** (`config/services.yaml`). User-facing strings should live in the `translations/` catalogs (currently `messages+intl-icu.fr.yaml`) rather than being hardcoded.

---

## 🗄 Database & migrations

The connection is driven by `DATABASE_URL` (Doctrine DBAL 4). ORM mapping uses PHP attributes (`type: attribute`) under `src/Entity`.

```bash
make connect                                   # PHP shell

# Create the schema from the migrations
php bin/console doctrine:migrations:migrate

# Generate a new migration after modifying an entity
php bin/console make:migration

# Load the fixtures (if implemented)
php bin/console doctrine:fixtures:load
```

> In the `test` environment, Doctrine automatically suffixes the database name (`_test…`) to isolate data.

---

## ✉️ Messenger & async emails

- `config/packages/messenger.yaml` defines the **`async`** transport (Doctrine DBAL, `MESSENGER_TRANSPORT_DSN`) and a **`failed`** transport (`failed` file).
- `Symfony\Component\Mailer\Messenger\SendEmailMessage` is routed to `async`: **emails are sent asynchronously** (via Mailpit in dev).
- Workers are supervised by **`supervisord`** in the PHP container (`docker/php/messenger-workers.conf` + `docker/php/run_php.sh`): they consume `async` and `failed` in a loop, with `autorestart` enabled.

Inspect/retry the queues when needed:

```bash
php bin/console messenger:failed:show
php bin/console messenger:failed:retry
```

---

## 🧪 Tests

PHPUnit 12 is configured via `phpunit.xml.dist` (bootstrap `tests/bootstrap.php`, `SymfonyTestsListener` listener). The test environment is forced via `APP_ENV=test`.

```bash
make connect

php bin/phpunit                              # the whole suite
php bin/phpunit tests/path/to/SomeTest.php   # a single file
php bin/phpunit --filter testMethodName      # a single method
```

Coverage is declared on `src/` (`<directory suffix=".php">src</directory>`).

---

## 🔍 Code quality

Three quality gates are configured and must be **green before any commit**:

| Tool | Command | Configuration |
|---|---|---|
| Static analysis | `vendor/bin/phpstan analyse` | `phpstan.dist.neon` — **level 6** (paths: `bin/`, `config/`, `public/`, `src/`, `tests/`). |
| Code style | `vendor/bin/phpcs` | `phpcs.xml.dist` — **PSR‑12** rule. |
| Auto-fix | `vendor/bin/phpcbf` | Automatically fixes detected PSR‑12 violations. |

Run all the gates:

```bash
make connect
vendor/bin/phpstan analyse
vendor/bin/phpcs
vendor/bin/phpcbf   # if fixes are needed
php bin/phpunit
```

---

## 🐳 Docker

Orchestration is described in `docker-compose.yaml` (4 services):

| Service | Image / build | Role |
|---|---|---|
| `database` | `postgres:18.2-alpine` | PostgreSQL 18, `pg_isready` healthcheck, persistent volume. |
| `php` | build `docker/php` | PHP 8.5 FPM + Composer + Xdebug + extensions (pgsql, intl, apcu, sodium…). Supervisord runs FPM + Messenger workers. |
| `nginx` | `nginx:1.29.5-alpine` | FPM reverse proxy, `public/index.php` front controller. |
| `mailer` | `axllent/mailpit` | SMTP sink + web interface. |

Notable points:

- The PHP image is built from `php:8.5-fpm` with `install-php-extensions` for reproducible builds.
- The `docker` user (NOPASSWD sudo) avoids permission issues with mounted volumes.
- `php` depends on `database` (`service_healthy` condition); `nginx` depends on `php`.

To rebuild from scratch (e.g. after a PHP version change):

```bash
docker compose down -v      # also removes volumes (DATABASE LOST)
make install                # rebuild + install + start
```

---

## 🤝 Contribution

1. Fork the repository and create a feature branch (`git checkout -b feat/my-feature`).
2. Develop by extending the base classes and following the conventions (`fr` locale, no hardcoded strings).
3. Make sure the quality gates pass (see [Code quality](#-code-quality)).
4. Open a Pull Request to `main` with a clear description.

Please keep the default `.env` neutral and document any new environment variable in this README.

---

## 📄 License

Distributed under the **MIT** license — see the [LICENSE](LICENSE) file.
Copyright © 2023 Louise SOULIER.

---

## 📚 Additional documentation

- [`docs/UPGRADE_SYMFONY8.md`](docs/UPGRADE_SYMFONY8.md) — report and upgrade plan to Symfony 8.x / PHP 8.5.
- [`AGENTS.md`](AGENTS.md) — guidelines for AI coding agents working on this repository.
