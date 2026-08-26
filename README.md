<h1 align="center">Starter Kit — Symfony 8.1 (API Platform 4) + React 19 · Docker</h1>

<p align="center">
  <a href="https://www.php.net/releases/8.5/en.php"><img alt="PHP 8.5" src="https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php&logoColor=white" /></a>
  <a href="https://symfony.com/releases/8.1"><img alt="Symfony 8.1" src="https://img.shields.io/badge/Symfony-8.1-000000?style=flat-square&logo=symfony&logoColor=white" /></a>
  <a href="https://api-platform.com"><img alt="API Platform 4" src="https://img.shields.io/badge/API%20Platform-4-0e83cd?style=flat-square" /></a>
  <a href="https://react.dev"><img alt="React 19" src="https://img.shields.io/badge/React-19-61DAFB?style=flat-square&logo=react&logoColor=white" /></a>
  <a href="https://vite.dev"><img alt="Vite" src="https://img.shields.io/badge/Vite-8-646CFF?style=flat-square&logo=vite&logoColor=white" /></a>
  <a href="https://tailwindcss.com"><img alt="Tailwind CSS 4" src="https://img.shields.io/badge/Tailwind-4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" /></a>
  <a href="https://www.postgresql.org/"><img alt="PostgreSQL 18" src="https://img.shields.io/badge/PostgreSQL-18-336791?style=flat-square&logo=postgresql&logoColor=white" /></a>
  <a href="https://github.com/lsoulier42/react-symfony-starter/actions"><img alt="CI" src="https://github.com/lsoulier42/react-symfony-starter/actions/workflows/ci.yml/badge.svg" /></a>
  <a href="LICENSE"><img alt="License: MIT" src="https://img.shields.io/badge/License-MIT-2ea44f?style=flat-square" /></a>
</p>

<p align="center">
  A decoupled starter kit: a containerized <strong>API-only Symfony 8.1</strong> backend
  (PHP 8.5 FPM, <strong>API Platform 4</strong>, JWT auth) and a <strong>React 19</strong> SPA
  (Vite, Tailwind CSS 4, lucide icons) — login, registration, user profile and
  admin user-management screens included.
</p>

---

## 📚 Table of Contents

- [Overview](#-overview)
- [Key features](#-key-features)
- [Tech stack](#-tech-stack)
- [Architecture](#-architecture)
- [Prerequisites](#-prerequisites)
- [Quick start](#-quick-start)
- [Services & URLs](#-services--urls)
- [API reference](#-api-reference)
- [Frontend](#-frontend)
- [Database & migrations](#-database--migrations)
- [Commands (Makefile)](#-commands-makefile)
- [Tests](#-tests)
- [Code quality](#-code-quality)
- [Docker](#-docker)
- [License](#-license)

---

## 📖 Overview

This repository is a **starter kit** for building a modern web application with a
separated API and frontend:

- a **backend** in Symfony 8.1 running in **API-only mode** with **API Platform 4**:
  one `User` resource (admin CRUD), a public registration endpoint, a JWT login,
  and a `/api/me` profile endpoint;
- a **frontend** in **React 19 + Vite + Tailwind CSS 4 + lucide-react** with four
  screens: **login**, **registration**, **user profile** (read/update own data) and
  **admin user management** (search, pagination, enable/disable, promote/demote, delete);
- the same production origin for the SPA and the API: Nginx serves the built React
  app and proxies `/api` to PHP-FPM — **no CORS configuration needed**; in development,
  the Vite dev server proxies `/api` to the Symfony container.

> The backend keeps the opinionated base classes (`AbstractEntity`,
> `AbstractRepository`, traits, fixtures, helpers) and the strict quality gates
> (PHPStan level 6, PHPCS PSR-12, PHPUnit 12). Build your own business entities
> and screens on top.

---

## ✨ Key features

- **API-first backend**: API Platform 4 resources, `json`/JSON-LD formats, built-in
  pagination (`totalItems`, `member`, `view.first/last/next`) and filtering.
- **JWT authentication** (LexikJWTAuthenticationBundle): `POST /api/login` returns a
  token; the `api` firewall is **stateless**; a custom `UserChecker` rejects disabled
  accounts at authentication time.
- **Public registration**: validated DTO (`RegisterInput`) with unique-email check,
  password hashing and forced `ROLE_USER`.
- **Profile endpoint**: `GET /api/me` / `PATCH /api/me` (update email, names and
  optionally the password).
- **Admin user management**: `GET/PATCH/DELETE /api/users` protected by `ROLE_ADMIN`
  with email/name search filters and a boolean `isActive` filter.
- **React 19 SPA**: TypeScript, React Router 7, TanStack Query 5, axios with a Bearer
  interceptor and automatic 401 → `/login` redirect; Tailwind CSS 4 design system
  with lucide icons; guards (`RequireAuth`, `RequireRole ROLE_ADMIN`).
- **Same-origin in production**: Nginx serves the SPA and proxies the API, so there
  is no CORS and no exposed credentials beyond the JWT in `localStorage`.
- **Full containerization**: PHP 8.5 FPM, Nginx, PostgreSQL 18, Mailpit and an
  optional Vite container (`node:24-alpine`).
- **Strict quality gates**: PHPStan level 6, PHP_CodeSniffer (PSR-12), PHPUnit 12,
  ESLint and `tsc` for the frontend.

---

## 🧱 Tech stack

| Area | Technology | Version |
|---|---|---|
| Language | PHP (FPM) | **8.5** |
| Framework | Symfony | **8.1** |
| API | API Platform | **4.x** |
| Auth | Lexik JWT (stateless) | **3.x** |
| Database | PostgreSQL | **18** (`postgres:18.2-alpine`) |
| ORM | Doctrine ORM / DBAL | **3.6** / **4.4** |
| Web server | Nginx | **1.29** (`1.29.5-alpine`) |
| Mail (dev) | Mailpit | `axllent/mailpit` |
| Frontend framework | React (+ TypeScript) | **19.x** |
| Frontend tooling | Vite | **8.x** |
| Styling | Tailwind CSS | **4.x** (`@tailwindcss/vite`) |
| Icons | lucide-react | latest |
| Data fetching | TanStack Query / axios | **5.x** / 1.x |
| Routing | React Router | **7.x** |
| Quality (back) | PHPStan / PHP_CodeSniffer / PHPUnit | **2.x** / **3.7** / **12** |
| Quality (front) | ESLint / TypeScript | 9.x / 5.x |

---

## 🏗 Architecture

```
            ┌────────────────────────  browser  ────────────────────────┐
            │                                                            │
  DEV: Vite dev server (:5173, HMR)                   PROD: Nginx (:8081)
  proxy /api ──────────────┐                              │
       ┌───────────────────▼──────┐                       ├─ /    → frontend/dist (SPA)
       │  Symfony 8.1 (API only)  │  ── /api ─────────────┘
       │  API Platform 4          │  /api/login, /api/register
       │  JWT (Lexik)             │  /api/me (profile)
       │  Doctrine ORM 3          │  /api/users (admin CRUD)
       │  PostgreSQL 18 · Mailpit │
       └──────────────────────────┘
```

- **Monorepo**: the Symfony backend lives at the repository root; the React app
  lives in [`frontend/`](frontend/).
- Both environments are single-origin, so **no CORS** is required anywhere.
- The SPA only renders screens; **every authorization is enforced server-side**
  (firewall + API Platform `security` + `UserChecker`).

---

## 📋 Prerequisites

- **Docker** ≥ 24 and **Docker Compose** (v2, included in the Docker CLI).
- **Make** to run the `Makefile` recipes.
- **Node.js** ≥ 22 and **npm** for the frontend recipes (or use the `frontend`
  Docker service).
- **Git** to clone the repository.
- No local PHP/Composer/PostgreSQL installation is required: everything runs in
  the containers.

> ℹ️ On Linux, make sure your user belongs to the `docker` group (otherwise prefix
> the commands with `sudo`).

---

## 🚀 Quick start

```bash
git clone <this-repository> react-symfony-starter
cd react-symfony-starter

# 1. Build the images, install backend + frontend dependencies, start everything
#    and generate the JWT keypair (skipped if already present)
make install

# 2. Initialize the database
make migrate
make fixtures          # admin@example.com / password, user1..3@example.com / password, inactive@example.com / password

# 3. Open the SPA (Vite dev server, proxies /api to the backend)
#    → http://localhost:5173
#    API documentation (Swagger UI) is served by the backend
#    → http://localhost:8081/api/docs
```

Sign in with `admin@example.com` / `password` (admin) or `user1@example.com` /
`password` (regular user).

> The frontend runs through Vite on the host (`make frontend-dev`). For a fully
> containerized dev environment, start the dedicated service instead:
> `docker compose up -d frontend` → http://localhost:5173.

---

## 🌐 Services & URLs

| Service | URL / host | Notes |
|---|---|---|
| Frontend (dev, Vite + HMR) | `http://localhost:5173` | React 19 SPA, proxies `/api`. |
| Frontend (prod, built) | `http://localhost:8081` | Served by Nginx after `make frontend-build`. |
| Backend API | `http://localhost:8081/api` | API Platform + custom endpoints. |
| Swagger UI | `http://localhost:8081/api/docs` | Interactive API documentation. |
| Mailpit (web interface) | `http://localhost:1181` | Dev email viewer. |
| PostgreSQL (host) | `localhost:5532` | Mapped port from the `database` container. |
| Mailpit SMTP (host) | `localhost:1126` | Mapped SMTP port (internal `1025`). |

---

## 🌐 API reference

### Public endpoints

| Method | Path | Description |
|---|---|---|
| `POST` | `/api/login` | JSON `{email, password}` → `{token}` (JWT, 1h TTL). |
| `POST` | `/api/register` | `{email, plainPassword, firstName?, lastName?}` → 201 + user. | 

### Authenticated endpoints

| Method | Path | Access | Description |
|---|---|---|---|
| `GET` | `/api/me` | any authenticated user | Current profile (`me:read`). |
| `PATCH` | `/api/me` | any authenticated user | Update email/names, optional `plainPassword`. |

### Admin endpoints (API Platform resource `User`)

| Method | Path | Access | Description |
|---|---|---|---|
| `GET` | `/api/users` | `ROLE_ADMIN` | Paginated list. Filters: `email` (partial), `isActive` (`true`/`false`). Pagination: `page`, `itemsPerPage` (max 100). |
| `GET` | `/api/users/{id}` | `ROLE_ADMIN` | Single user. |
| `PATCH` | `/api/users/{id}` | `ROLE_ADMIN` | Update `email`, `firstName`, `lastName`, `roles`, `isActive`. |
| `DELETE` | `/api/users/{id}` | `ROLE_ADMIN` | Delete the account (204). |

Response shape of the collection (JSON-LD):

```json
{
  "@context": "/api/contexts/User",
  "@id": "/api/users",
  "@type": "Collection",
  "totalItems": 42,
  "member": [ { "@id": "/api/users/1", "email": "…", "roles": ["ROLE_USER"], "isActive": true } ],
  "view": { "@id": "/api/users?itemsPerPage=10&page=1", "first": "…", "last": "…", "next": "…" }
}
```

Error handling: validation failures return **422** with
`{"violations": [{"propertyPath": "email", "message": "…"}]}`; bad credentials
return **401**; missing admin rights return **403**. The `password` hash is never
serialized.

Auth flow for the SPA: `POST /api/login` → store the JWT in `localStorage`
(`starter_token`) → the axios instance attaches `Authorization: Bearer <token>` →
any 401 clears the token and redirects to `/login`.

---

## ⚙️ Configuration

All per-environment configuration is centralized in **`.env`** (committed defaults,
overridable via `.env.local`, not versioned). The test environment uses `.env.test`.

| Variable | Default | Description |
|---|---|---|
| `APP_ENV` | `dev` | Symfony environment (`dev` / `prod` / `test`). |
| `APP_SECRET` | `6bd8b04b…` | Application secret key. |
| `APP_PORT` | `8081` | Host port exposed by Nginx. |
| `FRONTEND_PORT` | `5173` | Host port exposed by the Vite dev server. |
| `DATABASE_*` | `root/password/symfony` | PostgreSQL credentials and database name. |
| `DATABASE_URL` | `postgresql://…` | Doctrine DSN built from the variables above. |
| `JWT_SECRET_KEY` / `JWT_PUBLIC_KEY` | `config/jwt/*.pem` | Lexik JWT keypair paths (generated by `make jwt`). |
| `JWT_PASSPHRASE` | random | Lexik JWT passphrase. |
| `MAILER_DSN` | `smtp://mailer:1025` | Mailer transport (Mailpit). |
| `MESSENGER_TRANSPORT_DSN` | `doctrine://default?auto_setup=0` | Messenger async transport (Doctrine DBAL). |

---

## 🗂 Project structure

```
.
├── bin/                      # Executables (console, composer, phpunit)
├── config/                   # Symfony configuration
│   ├── packages/
│   │   ├── api_platform.yaml # API Platform 4 (pagination, cache headers)
│   │   └── security.yaml     # Stateless `api` firewall (jwt + json_login)
│   └── routes.yaml           # /api/login route + attribute routes
├── docker/
│   ├── nginx/default.conf    # SPA static + /api → PHP-FPM
│   └── php/                  # PHP 8.5 image, supervisord, entrypoint
├── frontend/                 # React 19 SPA (Vite + Tailwind 4 + lucide)
│   ├── src/
│   │   ├── api/              # axios client, typed auth/users services
│   │   ├── auth/             # AuthContext, useAuth, RequireAuth/RequireRole
│   │   ├── components/       # ui.tsx design-system primitives
│   │   ├── layouts/          # AppLayout (header, role-aware nav)
│   │   ├── pages/            # Login, Register, Profile, admin/Users
│   │   └── lib/              # utils (cn, parseJwt, formatDate…)
│   ├── vite.config.ts        # React + Tailwind plugins, /api proxy
│   └── package.json
├── migrations/               # Doctrine migrations
├── public/                   # Symfony front controller (API only)
├── src/
│   ├── Controller/           # RegistrationController, MeController
│   ├── Entity/               # AbstractEntity, User (API Platform resource)
│   ├── Repository/           # AbstractRepository, UserRepository
│   ├── Dto/                  # RegisterInput, MePayload (validated inputs)
│   ├── Security/             # UserChecker (rejects disabled accounts)
│   ├── OpenApi/              # JWT security scheme for Swagger UI
│   ├── DataFixtures/         # Admin + users fixtures
│   └── Kernel.php
├── tests/
│   ├── AbstractApiTestCase.php  # ApiTestCase + Foundry (ResetDatabase)
│   └── Api/                    # Login, Registration, Me, AdminUsers tests
├── docker-compose.yaml        # database, php, nginx, frontend (dev), mailer
├── Makefile
├── composer.json
└── phpstan.dist.neon / phpcs.xml.dist / phpunit.xml.dist
```

---

## 🖥 Frontend

The SPA lives in [`frontend/`](frontend/) and is kept intentionally small:

| Area | Stack |
|---|---|
| Build | Vite 8 + TypeScript strict |
| UI | Tailwind CSS 4 (`@import "tailwindcss"` + `@theme`), lucide-react icons |
| Routing | React Router 7 (`/login`, `/register`, `/profile`, `/admin/users`) |
| Server state | TanStack Query 5 (`useQuery`/`useMutation`, cache invalidation) |
| HTTP | axios instance (`/api` base URL, Bearer interceptor, 401 → `/login`) |
| Auth | `AuthProvider` (JWT in `localStorage`), `useAuth`, `RequireAuth` / `RequireRole` |

```bash
cd frontend
npm install
npm run dev        # http://localhost:5173 (proxies /api → http://localhost:8081)
npm run build      # tsc --noEmit && vite build → dist/ (served by Nginx in prod)
npm run lint       # ESLint
```

### Screens

- **Login** (`/login`) — email + password, redirect by role (admin → `/admin/users`,
  user → `/profile`), sign-up link.
- **Registration** (`/register`) — email, optional first/last name, password +
  confirmation; success redirects to login with a banner.
- **Profile** (`/profile`, `RequireAuth`) — `GET /api/me` data, edit form
  (`PATCH /api/me`), optional password change.
- **Users** (`/admin/users`, `RequireAuth` + `ROLE_ADMIN`) — paginated table with
  email search and status filter, enable/disable, promote/demote admin, delete with
  confirmation.

### Design system

The whole UI (user and admin) shares one strict dark design system, defined as
Tailwind CSS 4 tokens in `src/index.css` (`@theme`):

| Token | Value |
|---|---|
| App background / stacked surfaces | `#0e1117` · `#161a22` → `#1c212c` → `#232a38` |
| Borders | `#262d3b` (subtle) · `#323c4f` (strong) |
| Primary / hover / accent | `#6c8cff` · `#5276f2` · `#a78bfa` |
| Semantics | success `#34d399` · warning `#fbbf24` · danger `#f87171` |
| Text | `#e2e8f0` (ink) · `#8b93a5` (muted) · `#5c6474` (faint) |
| Radii | 14px cards · 10px components · 0.6rem fields |
| Shadows | `0 8px 24px rgba(0,0,0,.3)` cards · soft `0 4px 12px` |
| Layout | sidebar 264px (icon bar < 992px) · content max 1320px · glass topbar (`backdrop-blur`) |

Hover/active states use soft translucencies (`bg-primary/12`, `bg-white/5` …);
cards have a slightly contrasted header, tables use uppercase `tracking-widest`
headers, badges are pills and row actions are square icon buttons.

---

## 🗄 Database & migrations

The connection is driven by `DATABASE_URL` (Doctrine DBAL 4). ORM mapping uses PHP
attributes (`type: attribute`) under `src/Entity`.

```bash
make connect                                   # PHP shell

php bin/console doctrine:migrations:migrate    # apply
php bin/console make:entity                    # modify an entity, then:
php bin/console make:migration                 # generate the migration
php bin/console doctrine:fixtures:load         # load fixtures
```

> Shortcuts: `make migrate` and `make fixtures` run the same commands with
> `--no-interaction`. In the `test` environment, Doctrine automatically suffixes
> the database name (`_test…`) to isolate data.

---

## 🛠 Commands (Makefile)

| Command | Description |
|---|---|
| `make install` | Build images + `composer install` + `npm install` + start + generate JWT keys. |
| `make start` / `make stop` / `make restart` | Start / stop / restart the containers. |
| `make connect` | Shell in the PHP container. |
| `make clear` | `cache:clear` in the PHP container. |
| `make jwt` | Generate the Lexik JWT keypair (skipped if it exists). |
| `make frontend-install` | `npm install` in `frontend/`. |
| `make frontend-dev` | Start the Vite dev server (HMR) on `:5173`. |
| `make frontend-build` | Type-check and build the SPA into `frontend/dist`. |
| `make frontend-lint` | ESLint on the SPA. |
| `make composer-install` / `make composer-update` | Composer in the PHP container. |
| `make migrate` / `make fixtures` | Migrations / fixtures (`--no-interaction`). |
| `make test` | PHPUnit suite in the container. |
| `make phpstan` | PHPStan static analysis (level 6). |
| `make cs` / `make csfix` | PHP_CodeSniffer (PSR-12) / auto-fix. |
| `make logs` | Follow container logs. |
| `make destroy` | Remove containers and volumes (`docker compose down -v`). |

---

## 🧪 Tests

PHPUnit 12 is configured via `phpunit.xml.dist`. Tests use
[`ApiTestCase`](https://api-platform.com/docs/core/testing/) (API Platform test
client) plus Zenstruck Foundry (`ResetDatabase` + `Factories`).

```bash
make test
```

Coverage is declared on `src/` (`<source>`). The suite covers the whole API:
registration (201, 422 duplicates/invalid payloads, forced role), login (token,
401 wrong credentials, 401 disabled account), profile (`/api/me` GET/PATCH,
password change, email conflict) and admin user management (401/403 guards,
pagination, filters, enable/disable, role change, delete).

## 🔍 Code quality

| Tool | Command | Configuration |
|---|---|---|
| Static analysis (PHP) | `vendor/bin/phpstan analyse` | `phpstan.dist.neon` — level 6. |
| Code style (PHP) | `vendor/bin/phpcs` | `phpcs.xml.dist` — PSR-12. |
| Unit/API tests | `php bin/phpunit` | `phpunit.xml.dist` — PHPUnit 12. |
| Lint (frontend) | `npm run lint` (in `frontend/`) | ESLint (typescript-eslint, react-hooks). |
| Types/build (frontend) | `npm run build` (in `frontend/`) | `tsc --noEmit` + `vite build`. |

All gates must be green before committing.

---

## 🐳 Docker

Orchestration is described in `docker-compose.yaml` (5 services):

| Service | Image / build | Role |
|---|---|---|
| `database` | `postgres:18.2-alpine` | PostgreSQL 18, `pg_isready` healthcheck, persistent volume. |
| `php` | build `docker/php` | PHP 8.5 FPM + Composer + Xdebug + extensions (pgsql, intl, apcu, sodium…). Supervisord manages the Messenger workers. |
| `nginx` | `nginx:1.29.5-alpine` | Serves the built SPA and proxies `/api` → PHP-FPM. |
| `frontend` | `node:24-alpine` (dev) | Vite dev server with HMR (optional; the host `npm run dev` works too). |
| `mailer` | `axllent/mailpit` | SMTP sink + web interface. |

Notable points:

- The PHP image is built from `php:8.5-fpm` with `install-php-extensions` for
  reproducible builds.
- The `docker` user (NOPASSWD sudo) avoids permission issues with mounted volumes.
- `php` depends on `database` (`service_healthy` condition); `nginx` and
  `frontend` depend on `php`.
- The frontend container keeps `node_modules` in a named volume so the dev-server
  install matches the container's libc (musl) — the host and container installs
  never clash.

To rebuild from scratch:

```bash
docker compose down -v      # also removes volumes (DATABASE + frontend node_modules LOST)
make install
```

---

## 📄 License

Distributed under the **MIT** license — see the [LICENSE](LICENSE) file.
Copyright © 2023 Louise SOULIER.