<h1 align="center">Starter Kit — Symfony 8.1 · PHP 8.5 · Docker</h1>

<p align="center">
  <a href="https://www.php.net/releases/8.5/en.php"><img alt="PHP 8.5" src="https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php&logoColor=white" /></a>
  <a href="https://symfony.com/releases/8.1"><img alt="Symfony 8.1" src="https://img.shields.io/badge/Symfony-8.1-000000?style=flat-square&logo=symfony&logoColor=white" /></a>
  <a href="https://www.postgresql.org/"><img alt="PostgreSQL 18" src="https://img.shields.io/badge/PostgreSQL-18-336791?style=flat-square&logo=postgresql&logoColor=white" /></a>
  <a href="https://www.docker.com/"><img alt="Docker" src="https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white" /></a>
  <a href="LICENSE"><img alt="License: MIT" src="https://img.shields.io/badge/License-MIT-2ea44f?style=flat-square" /></a>
</p>

<p align="center">
  Squelette d'application <strong>Symfony 8.1</strong> conteneurisé (PHP 8.5 FPM), prêt pour la production,
  avec locale française par défaut, portes de qualité strictes (PSR‑12 + PHPStan niveau 6)
  et un jeu de classes de base opinionnées pour démarrer rapidement.
</p>

---

## 📚 Table des matières

- [Présentation](#-présentation)
- [Fonctionnalités clés](#-fonctionnalités-clés)
- [Stack technique](#-stack-technique)
- [Prérequis](#-prérequis)
- [Démarrage rapide](#-démarrage-rapide)
- [Configuration](#-configuration)
- [Commandes (Makefile)](#-commandes-makefile)
- [Services & URLs](#-services--urls)
- [Structure du projet](#-structure-du-projet)
- [Architecture & conventions](#-architecture--conventions)
- [Base de données & migrations](#-base-de-données--migrations)
- [Messenger & emails asynchrones](#-messenger--emails-asynchrones)
- [Tests](#-tests)
- [Qualité de code](#-qualité-de-code)
- [Docker](#-docker)
- [Contribution](#-contribution)
- [Licence](#-licence)
- [Documentation complémentaire](#-documentation-complémentaire)

---

## 📖 Présentation

Ce dépôt est un **starter kit** (point de départ) pour construire une application web Symfony moderne, entièrement exécutée dans des conteneurs Docker. Il fournit :

- un environnement de développement reproductible (PHP 8.5 FPM, Nginx, PostgreSQL 18, Mailpit) ;
- une configuration Symfony 8.1 éprouvée (Doctrine ORM 3, Messenger, Mailer, AssetMapper, Stimulus, Twig, Security, Validator, Translator) ;
- des **classes de base** (`AbstractBaseController`, `AbstractEntity`, `AbstractRepository`, `AbstractFixtures`) et **utilitaires** (`Helper/`, `Dto/`, `Trait/`) pour accélérer le développement ;
- des portes de qualité de code intégrées (PHPStan niveau 6, phpcs PSR‑12, PHPUnit 12).

> Le projet démarre avec une page d'accueil (`HomepageController`) et une architecture « vide mais structurée » : aucune entité métier n'est livrée, à vous de l'enrichir.

---

## ✨ Fonctionnalités clés

- **Conteneurisation complète** : PHP (FPM), Nginx, PostgreSQL et Mailpit orchestrés via `docker compose`.
- **Worker Messenger supervisé** : consommation asynchrone des messages (emails, tâches) via `supervisord` dans le conteneur PHP.
- **Emails en local sans friction** : Mailpit capte tous les emails sortants et propose une interface web de debug.
- **Pagination prête à l'emploi** : intégration Pagerfanta via `AbstractRepository::findAllPaginated()` + `PaginationDto`.
- **Entités « timestampées » et UUID** : `AbstractEntity` + `Trait\Timestampable` (gestion automatique de `createdAt`/`updatedAt` et d'un `Uuid` v4).
- **Helpers métier** : conversion d'objets (`ClassConverterHelper`), formatage de dates en français (`DateTimeHelper`).
- **Locale française par défaut** : `config/services.yaml` (`locale: fr`) et catalogues de traduction dans `translations/`.
- **Qualité de code stricte** : PHPStan niveau 6, PHP_CodeSniffer (PSR‑12), PHPUnit 12.

---

## 🧱 Stack technique

| Domaine | Technologie | Version |
|---|---|---|
| Langage | PHP (FPM) | **8.5** |
| Framework | Symfony | **8.1** |
| Base de données | PostgreSQL | **18** (`postgres:18.2-alpine`) |
| Serveur web | Nginx | **1.29** (`1.29.5-alpine`) |
| Mail (dev) | Mailpit | `axllent/mailpit` |
| ORM | Doctrine ORM / DBAL | **3.6** / **4.4** |
| Pagination | Pagerfanta | **4.x** |
| Frontend | AssetMapper + Stimulus + Bootstrap | Symfony 8.1 assets |
| Qualité | PHPStan / PHP_CodeSniffer / PHPUnit | **2.x** / **3.7** / **12** |

---

## 📋 Prérequis

- **Docker** ≥ 24 et **Docker Compose** (v2, inclus dans la CLI Docker).
- **Make** (utilitaire `make`) pour exécuter les recettes du `Makefile`.
- **Git** pour cloner le dépôt.
- Aucune installation locale de PHP/Composer/PostgreSQL n'est requise : tout s'exécute dans les conteneurs.

> ℹ️ Sous Linux, assurez-vous que votre utilisateur appartient au groupe `docker` (sinon préfixez les commandes avec `sudo`).

---

## 🚀 Démarrage rapide

```bash
# 1. Cloner le dépôt
git clone git@github.com:lsoulier42/symfony-docker-starter.git
cd symfony-docker-starter

# 2. Construire les images, installer les dépendances et démarrer
make install

# 3. Ouvrir l'application
#    → http://localhost:8081
```

`make install` enchaîne automatiquement : `docker compose build`, `composer install`, `importmap:install` et `docker compose up -d`.

Pour initialiser la base de données (une fois les conteneurs démarrés) :

```bash
make connect                      # shell dans le conteneur PHP
php bin/console doctrine:migrations:migrate
```

---

## ⚙️ Configuration

Toute la configuration par environnement est centralisée dans **`.env`** (valeurs par défaut commitées) et peut être surchargée localement par **`.env.local`** (non versionné). L'environnement de test utilise **`.env.test`**.

| Variable | Valeur par défaut | Description |
|---|---|---|
| `APP_ENV` | `dev` | Environnement Symfony (`dev` / `prod` / `test`). |
| `APP_SECRET` | `6bd8b04b…` | Clé secrète de l'application. |
| `APP_VERSION` | `0.1.0` | Version de l'application (exposée via le paramètre `app_version`). |
| `APP_PORT` | `8081` | Port hôte exposé par Nginx. |
| `DATABASE_HOST` | `database` | Nom du service PostgreSQL (réseau Docker). |
| `DATABASE_PORT` | `5432` | Port interne PostgreSQL. |
| `DATABASE_USER` | `root` | Utilisateur PostgreSQL. |
| `DATABASE_PASSWORD` | `password` | Mot de passe PostgreSQL. |
| `DATABASE_NAME` | `symfony6-docker` | Nom de la base (nom historique, voir note). |
| `DATABASE_URL` | `postgresql://…` | DSN Doctrine construit à partir des variables ci‑dessus. |
| `MAILER_DSN` | `smtp://mailer:1025` | Transport Mailer (Mailpit). |
| `MESSENGER_TRANSPORT_DSN` | `doctrine://default?auto_setup=0` | Transport async Messenger (Doctrine DBAL). |

> ⚠️ **Note de cohérence** : le nom de la base est `symfony6-docker` (héritage d'un rebranding). Il est volontairement identique entre `.env` et le code pour éviter toute rupture ; vous pouvez le renommer globalement si besoin.

---

## 🛠 Commandes (Makefile)

Toutes les opérations courantes passent par `make` (les commandes Composer/PHP s'exécutent **dans** le conteneur PHP).

| Commande | Description |
|---|---|
| `make install` | Build des images + `composer install` + assets + démarrage complet. |
| `make start` | Démarre les conteneurs en arrière‑plan. |
| `make start-verbose` | Démarre les conteneurs et affiche les logs en direct. |
| `make stop` | Arrête et supprime les conteneurs (`docker compose down`). |
| `make connect` | Ouvre un shell Bash dans le conteneur PHP. |
| `make clear` | Vide le cache Symfony (`cache:clear`). |
| `make composer-install` | `composer install` (memory limit élevée) dans le conteneur. |
| `make composer-update` | `composer update -W` dans le conteneur. |
| `make assets-install` | Installe les dépendances front via `importmap:install`. |
| `make assets-compile` | Compile les assets pour la production (`asset-map:compile`). |

---

## 🌐 Services & URLs

| Service | URL / hôte | Remarque |
|---|---|---|
| Application (dev) | `http://localhost:8081` | Servie par Nginx + PHP‑FPM. |
| Mailpit (interface web) | `http://localhost:1181` | Visualiseur d'emails de dev. |
| PostgreSQL (hôte) | `localhost:5532` | Port mappé depuis le conteneur `database`. |
| SMTP Mailpit (hôte) | `localhost:1126` | Port SMTP mappé (interne `1025`). |

---

## 🗂 Structure du projet

```
.
├── bin/                      # Exécutables (console, composer, phpunit)
├── config/                  # Configuration Symfony (packages/, services.yaml, routes…)
├── docker/
│   ├── db/                  # Volume de données PostgreSQL
│   ├── nginx/default.conf   # Configuration Nginx (PHP‑FPM, front controller)
│   └── php/                 # Dockerfile PHP, supervisord (workers Messenger), entrypoint
├── migrations/              # Migrations Doctrine
├── public/                  # Document root (index.php, favicon…)
├── src/
│   ├── Controller/          # Contrôleurs (AbstractBaseController + HomepageController)
│   ├── Entity/              # Entités Doctrine (AbstractEntity)
│   ├── Repository/          # Repositories (AbstractRepository)
│   ├── Dto/                 # PaginationDto
│   ├── Helper/              # DateTimeHelper, ClassConverterHelper
│   ├── Trait/               # Timestampable
│   ├── DataFixtures/        # AbstractFixtures
│   └── Kernel.php
├── templates/               # Templates Twig (base, layout, homepage)
├── assets/                  # Sources front (JS/CSS, importmap, Stimulus)
├── tests/                   # Tests PHPUnit (bootstrap.php)
├── translations/            # Catalogues de traduction (messages+intl-icu.fr.yaml)
├── docker-compose.yaml
├── Makefile
├── composer.json
└── phpstan.dist.neon / phpcs.xml.dist / phpunit.xml.dist
```

---

## 🏗 Architecture & conventions

L'objectif de ce starter kit est de **réduire le code répétitif** : étendez les classes de base plutôt que de tout réimplémenter.

### Classes de base à étendre

| Classe | Rôle |
|---|---|
| `App\Controller\AbstractBaseController` | Ajoute `createPaginationDto()`, `addSuccessMessage()`, `addWarningMessage()`, `addErrorMessage()`. |
| `App\Entity\AbstractEntity` | fournit `id` (SEQUENCE), `uuid` (Uuid v4) et le trait `Timestampable`. |
| `App\Repository\AbstractRepository` | Helpers DQL : `createOrUpdate()`, `remove()`, `addFieldLike()`, `addFieldAndWhere()`, `addTableJoin()`, `addPeriodWhere()`, `addRandomElements()`, `findAllPaginated()`. |
| `App\DataFixtures\AbstractFixtures` | Initialise un générateur `Faker` (`fr_FR`) pour les fixtures. |

### Helpers & DTO

- `App\Helper\DateTimeHelper` — `formatMonthYearFrench()` et constantes de mois français.
- `App\Helper\ClassConverterHelper` — `convertToClass()` pour copier les propriétés d'un objet vers un autre (via PropertyInfo / PropertyAccess).
- `App\Dto\PaginationDto` — payload de pagination (`page`, `limit`), compatible avec Pagerfanta.

### Autowiring & enregistrement des services

`config/services.yaml` active l'**autowiring** et l'**autoconfiguration** pour le préfixe `App\`. Tout ce qui se trouve sous `src/` est enregistré automatiquement **à l'exception de** :

- `src/Entity/` (entités Doctrine, exclues volontairement) ;
- `src/Kernel.php` ;
- `src/DependencyInjection/`.

### Locale & traductions

La locale par défaut est **`fr`** (`config/services.yaml`). Les chaînes utilisateur doivent être placées dans les catalogues `translations/` (actuellement `messages+intl-icu.fr.yaml`) plutôt que codées en dur.

---

## 🗄 Base de données & migrations

La connexion est pilotée par `DATABASE_URL` (Doctrine DBAL 4). Le mapping ORM utilise les attributs PHP (`type: attribute`) sous `src/Entity`.

```bash
make connect                                   # shell PHP

# Créer le schéma à partir des migrations
php bin/console doctrine:migrations:migrate

# Générer une nouvelle migration après modification d'une entité
php bin/console make:migration

# Charger les fixtures (si implémentées)
php bin/console doctrine:fixtures:load
```

> En environnement `test`, Doctrine suffixe automatiquement le nom de la base (`_test…`) pour isoler les données.

---

## ✉️ Messenger & emails asynchrones

- `config/packages/messenger.yaml` définit le transport **`async`** (Doctrine DBAL, `MESSENGER_TRANSPORT_DSN`) et un transport **`failed`** (file `failed`).
- `Symfony\Component\Mailer\Messenger\SendEmailMessage` est routé vers `async` : **les emails sont envoyés de façon asynchrone** (via Mailpit en dev).
- Les workers sont supervisés par **`supervisord`** dans le conteneur PHP (`docker/php/messenger-workers.conf` + `docker/php/run_php.sh`) : ils consomment `async` et `failed` en boucle, avec `autorestart` activé.

Consulter/relancer les files en cas de besoin :

```bash
php bin/console messenger:failed:show
php bin/console messenger:failed:retry
```

---

## 🧪 Tests

PHPUnit 12 est configuré par `phpunit.xml.dist` (bootstrap `tests/bootstrap.php`, écouteur `SymfonyTestsListener`). L'environnement de test est forcé via `APP_ENV=test`.

```bash
make connect

php bin/phpunit                              # toute la suite
php bin/phpunit tests/path/to/SomeTest.php   # un fichier
php bin/phpunit --filter testMethodName      # une méthode
```

Le coverage est déclaré sur `src/` (`<directory suffix=".php">src</directory>`).

---

## 🔍 Qualité de code

Trois portes de qualité sont configurées et doivent être **vertes avant tout commit** :

| Outil | Commande | Configuration |
|---|---|---|
| Analyse statique | `vendor/bin/phpstan analyse` | `phpstan.dist.neon` — **niveau 6** (chemins : `bin/`, `config/`, `public/`, `src/`, `tests/`). |
| Style de code | `vendor/bin/phpcs` | `phpcs.xml.dist` — règle **PSR‑12**. |
| Correction auto | `vendor/bin/phpcbf` | Corrige automatiquement les écarts PSR‑12 détectés. |

Exécuter l'ensemble des portes :

```bash
make connect
vendor/bin/phpstan analyse
vendor/bin/phpcs
vendor/bin/phpcbf   # si besoin de corriger
php bin/phpunit
```

---

## 🐳 Docker

L'orchestration est décrite dans `docker-compose.yaml` (4 services) :

| Service | Image / build | Rôle |
|---|---|---|
| `database` | `postgres:18.2-alpine` | PostgreSQL 18, healthcheck `pg_isready`, volume persistant. |
| `php` | build `docker/php` | PHP 8.5 FPM + Composer + Xdebug + extensions (pgsql, intl, apcu, sodium…). Supervisord lance FPM + workers Messenger. |
| `nginx` | `nginx:1.29.5-alpine` | Reverse proxy FPM, front controller `public/index.php`. |
| `mailer` | `axllent/mailpit` | Capteur SMTP + interface web. |

Points notables :

- L'image PHP est construite depuis `php:8.5-fpm` avec `install-php-extensions` pour des builds reproductibles.
- L'utilisateur `docker` (sudo NOPASSWD) évite les problèmes de permissions avec les volumes montés.
- `php` dépend de `database` (condition `service_healthy`) ; `nginx` dépend de `php`.

Pour reconstruire from scratch (ex. après changement de version PHP) :

```bash
docker compose down -v      # supprime aussi les volumes (BASE DE DONNÉES PERDUE)
make install                # rebuild + install + start
```

---

## 🤝 Contribution

1. Forkez le dépôt et créez une branche feature (`git checkout -b feat/ma-fonctionnalite`).
2. Développez en étendant les classes de base et en respectant les conventions (locale `fr`, pas de chaînes codées en dur).
3. Assurez-vous que les portes de qualité passent (voir [Qualité de code](#-qualité-de-code)).
4. Ouvrez une Pull Request vers `main` avec une description claire.

Merci de garder le `.env` par défaut neutre et de documenter toute nouvelle variable d'environnement dans ce README.

---

## 📄 Licence

Distribué sous la licence **MIT** — voir le fichier [LICENSE](LICENSE).
Copyright © 2023 Louise SOULIER.

---

## 📚 Documentation complémentaire

- [`docs/UPGRADE_SYMFONY8.md`](docs/UPGRADE_SYMFONY8.md) — compte‑rendu et plan de montée de version vers Symfony 8.x / PHP 8.5.
- [`AGENTS.md`](AGENTS.md) — directives destinées aux agents de codage IA travaillant sur ce dépôt.
