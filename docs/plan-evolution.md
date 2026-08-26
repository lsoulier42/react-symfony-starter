# Plan d'évolution — Starter kit « API Platform 4 (Symfony 8.1) + React 19 SPA »

> Document de travail — **décisions §9 validées ; plan mis en œuvre (phases 1 à 6 livrées)**.
> Version : 2.0

## 1. Contexte et décisions actées

Le dépôt actuel est un starter **monolithique** : Symfony 8.1 / PHP 8.5 rendu via Twig + AssetMapper/Stimulus, auth par formulaire avec session, et une petite API JSON « maison » (`AbstractApiController`, `MapRequestPayload`).

Décisions de l'évolution :

| Domaine | Décision |
|---|---|
| Backend | Symfony **8.1 / PHP 8.5** conservés, bascule en **mode API seule** avec **API Platform 4** |
| Frontend | Starter **React 19 + Vite + Tailwind + lucide-react**, SPA découplée |
| Écrans livrés | Login, inscription, **profil user** (accès aux données du profil), **gestion des inscrits côté admin** |

Références vérifiées : [API Platform 4.x — JWT Authentication with Symfony](https://api-platform.com/docs/v4.2/symfony/jwt/), [api-platform/core : support Symfony 8.0 (PR fusionnée)](https://github.com/api-platform/core/pull/7561), [API Platform — state processors](https://api-platform.com/docs/v4.2/core/state-processors/).

## 2. Architecture cible

```
            ┌────────────────────────  navigateur  ────────────────────────┐
            │                                                              │
   DEV : Vite dev server (5173, HMR)                       PROD : Nginx
   proxy /api ──────────────┐                                │
        ┌───────────────────▼──────┐                         ├─ /  → frontend/dist (SPA, lucide + Tailwind)
        │   Symfony 8.1 (API seule)│  ── /api ───────────────┘
        │   API Platform 4         │  /api/login, /api/register
        │   JWT (Lexik)            │  /api/me (profil)
        │   Doctrine ORM 3         │  /api/users (admin CRUD)
        │   Postgres 18 · Mailpit  │
        └──────────────────────────┘
```

- **Monorepo unique** (repo actuel conservé) : backend à la racine, nouveau dossier `frontend/`.
- **Même origine en prod** (Nginx sert la SPA *et* proxy `/api`) → **pas de CORS en prod** ; en dev, le **proxy Vite** vers `http://localhost:8081` évite aussi le CORS.
- Le frontend n'expose que des écrans ; **toute l'autorisation est re-vérifiée côté API**.

## 3. Ce qu'on garde / supprime / ajoute

| Garder | Supprimer (devenu inutile en mode API) | Ajouter |
|---|---|---|
| `User` (enrichi), `AbstractEntity`, `AbstractRepository`, traits, `Helper/`, Foundry, PHPStan/phpcs/PHPUnit, Docker (PHP/Postgres/Nginx/Mailpit), Messenger/Mailer | `templates/`, `assets/`, `importmap.php`, Stimulus, AssetMapper, fontawesome, `symfony/form`, Pagerfanta ; `HomepageController`, `LoginController`, `AdminController`, `UserApiController`, `AbstractBase/AbstractApiController` ; `UserPayload`, `PaginationDto` ; `form_login` + session | API Platform 4 + Lexik JWT, entité enrichie (`isActive`, prénom/nom), DTO `RegisterInput`, endpoints `/api/me`, dossier `frontend/` (React 19 + Vite + Tailwind 4 + lucide-react + React Router + TanStack Query), service Docker node, config Nginx SPA, recettes Makefile frontend |

## 4. Backend — étapes détaillées (phases 1 & 2)

1. **Dépendances** : `composer require api-platform/core` (vérifier compat Symfony 8.1 — [PR #7561 fusionnée](https://github.com/api-platform/core/pull/7561)) et `lexik/jwt-authentication-bundle` ; retirer `symfony/stimulus-bundle`, `symfony/asset-mapper`, `symfony/form`, `babdev/pagerfanta-bundle`, `pagerfanta/pagerfanta`, `symfony/asset`, `twig/extra-bundle` (Twig reste : requis par la Swagger UI d'API Platform).
2. **`config/packages/api_platform.yaml`** : titre/version, `formats: json` (JSON pur, pas de JSON-LD/HAL), pagination API Platform (page, items par page, max), mapping `src/Entity`.
3. **JWT (Lexik)** — chemin documenté API Platform : génération des clés (`lexik:jwt:generate-keypair`), `security.yaml` : firewall `api` (`^/api`, **stateless**, guard `jwt`, provider par email), **`user_checker`** custom (`App\Security\UserChecker`) qui refuse les comptes `isActive = false` (401) ; `access_control` : `PUBLIC_ACCESS` sur `/api/login`, `/api/register`, `/api/docs`, puis `IS_AUTHENTICATED_FULLY` sur `/api`. Suppression du firewall `main` (form_login/logout).
4. **Entité `User` enrichie** : ajout `firstName` / `lastName` (nullable, pour un profil et un tableau admin exploitables) et `isActive` (bool, défaut true) ; groupes de sérialisation `user:read` / `user:write` / `me:read` ; **jamais** de groupe sur `password` ; migration Doctrine ; mise à jour `UserFixtures` (admin + 2-3 users dont un inactif) et `UserFactory` Foundry.
5. **`POST /api/register`** (public) : contrôleur + `#[MapRequestPayload] RegisterInput` (DTO validé : email `NotBlank`+`Email`+`UniqueEntity` sur `User.email`, mot de passe ≥ 8, confirmation) → hash, rôle forcé `ROLE_USER`, `isActive = true`, réponse 201 `user:read`.
6. **`GET /api/me` + `PATCH /api/me`** (authentifié, n'importe quel rôle) : `#[CurrentUser]`, lecture du profil ; mise à jour email/prénom/nom et, optionnellement, changement de mot de passe (nouveau champ `plainPassword` optionnel → re-hash si présent). Groupes `me:read`/`me:write`.
7. **`User` en ressource API Platform `#[\ApiResource]`** (admin) : collection GET avec **filtres** (email, rôle, `isActive`) + pagination API Platform ; GET/PATCH/DELETE par uuid ; `security: "is_granted('ROLE_ADMIN')"` au niveau ressource (réponse 403 sinon) ; les rôles et `isActive` ne sont modifiables qu'ici (gestion des inscrits : suspendre/réactiver, changer rôle, supprimer).
8. **Nettoyage** : suppression des contrôleurs/DTO/base classes Twig cités en §3, de `templates/`, `assets/`, `importmap.php`, configs `asset_mapper.yaml`, `twig.yaml`, `csrf.yaml` ; `QueryBuilderHelper` dé-pagerfanta-ifié (ou conservé tel quel hors usages). `services.yaml` : exclusions ajustées.
9. **Tests API** (remplacement des tests Web) : `ApiTestCase` (composant test d'API Platform) + `ResetDatabase`/Foundry conservés — `RegisterTest` (201, 422 doublon email, 422 validation), `LoginTest` (200 + token, 401 mauvais creds, 401 user désactivé), `MeTest` (GET/PATCH avec token), `AdminUsersTest` (403 sans admin, liste/filtres/pagination, suspendre, changer rôle, supprimer).
10. **Critères de sortie phase backend** : `make phpstan` / `make cs` / `make test` verts ; Swagger UI (`http://localhost:8081/api/docs`) exploitable avec auth Bearer.

## 5. Frontend — étapes détaillées (phases 3 à 5)

1. **Scaffold** : `npm create vite@latest frontend -- --template react-ts` (**TypeScript**, recommandé) ; deps : `react-router` (v7, mode librairie), `@tanstack/react-query` (v5), `axios`, `tailwindcss` + `@tailwindcss/vite` (**Tailwind v4, CSS-first** : `@import "tailwindcss"` + thème `@theme`, pas de PostCSS ni de `tailwind.config`), `lucide-react`.
2. **`vite.config.ts`** : plugins `react()` + `tailwindcss()` ; `server.proxy: { '/api': 'http://localhost:8081' }` ; `build.outDir: 'dist'`.
3. **Structure `frontend/src/`** :
   - `api/` — instance axios (base `/api`, intercepteur `Authorization: Bearer <token>`, gestion 401 → redirection `/login`) + services typés (`authApi`, `meApi`, `usersApi`) ;
   - `auth/` — `AuthContext`/`useAuth` (token localStorage + payload décodé : email, rôles), gardes `RequireAuth` / `RequireRole` ;
   - `components/` — primitives maison Tailwind (Button, Input, Card, Alert, Badge, Spinner, DataTable, EmptyState) avec icônes lucide ;
   - `layouts/` — `AppLayout` (header/nav selon rôle : Profil · Administration, bouton Déconnexion, icônes lucide) ;
   - `pages/` — `LoginPage`, `RegisterPage`, `ProfilePage`, `admin/UsersPage` ;
   - `lib/` — `cn()`, `formatDate` (locale fr).
4. **Écrans** :
   - **Login** (`/login`) : email + mot de passe → `POST /api/login` → stockage token → redirection par rôle (admin → `/admin/users`, user → `/profile`) ; message d'erreur (401 « identifiants invalides » ou « compte désactivé ») ; lien vers l'inscription.
   - **Inscription** (`/register`) : email, prénom, nom, mot de passe + confirmation → `POST /api/register` → 201 → redirection `/login` avec message de succès.
   - **Profil** (`/profile`, `RequireAuth`) : `GET /api/me` affiché (email, prénom, nom), formulaire de mise à jour (`PATCH /api/me`), zone « changer le mot de passe » optionnelle.
   - **Admin — utilisateurs** (`/admin/users`, `RequireAuth` + `RequireRole ROLE_ADMIN`) : tableau `GET /api/users` paginé, recherche par email, badge rôle/statut, actions **suspendre/réactiver** (`isActive`), **changer le rôle**, **supprimer** (avec confirmation), icônes lucide (Search, Power, Shield, Trash2, Pencil…).
5. **Data fetching** : TanStack Query — hooks `useMe`, `useUsers(page, search)`, mutations `useUpdateMe`, `useToggleUserActive`, `useDeleteUser`, invalidation de cache après mutation.
6. **UX** : français par défaut (cohérent avec le backend), états loading/error/empty, design responsive simple (header + contenu centré pour l'auth, layout app pour le reste).
7. **Critères de sortie frontend** : `npm run dev` → proxy OK ; parcours complet : inscription → login → profil ; login admin → gestion des utilisateurs ; déconnexion ; `npm run build` + `npm run lint` verts.

## 6. Infra, Docker, Makefile, docs (phase 6)

1. **docker-compose** : service optionnel `frontend` (node:22-alpine, `npm ci`, `vite --host 0.0.0.0`, port 5173) pour un dev 100 % conteneurisé ; sinon Vite lancé sur l'hôte.
2. **Nginx prod** (`docker/nginx/default.conf`) : root `frontend/dist` ; `location /` → `try_files $uri /index.html` (fallback SPA) ; `location /api` → fastcgi PHP-FPM (`public/index.php`) ; exclusion des `.php` hors `/api`.
3. **Makefile** : `frontend-install`, `frontend-dev`, `frontend-build`, `frontend-lint`, `frontend-test`, `jwt` (génération clés Lexik dans le conteneur php) ; `install` actualisé ; suppression des recettes `importmap`.
4. **`.env`** : `JWT_PASSPHRASE`, `FRONTEND_PORT=5173`.
5. **README** : rewrite — architecture découplée, stack (API Platform 4, JWT, React 19, Vite, Tailwind 4, lucide), tableau des endpoints, flux d'auth, quick start (backend + frontend), quality gates (phpstan/phpcs/phpunit + eslint).
6. **Suppression finale** du code mort et vérification globale `make install` + parcours bout en bout.

## 7. Ordre d'implémentation & jalons

| Phase | Contenu | DoD |
|---|---|---|
| 1 | Backend : deps, config API Platform, JWT, entité enrichie | Swagger UI OK, auth Bearer OK, gates verts |
| 2 | Backend : registration, `/api/me`, ressource `User` admin, tests | Tests API verts, endpoints documentés |
| 3 | Frontend : scaffold Vite+React+Tailwind+lucide, proxy, design system | Page de login visible, proxy `/api` OK |
| 4 | Authentification : login, inscription, context, gardes | Parcours inscription→login→redirection OK |
| 5 | Profil + admin users | Parcours user et admin complets |
| 6 | Infra prod + Makefile + README + nettoyage | `make install` bout en bout, README à jour |

Chaque phase se termine par un commit propre et le passage des gates de qualité.

## 8. Risques & parades

- **API Platform 4 × Symfony 8.1** : support Symfony 8 fusionné ([PR #7561](https://github.com/api-platform/core/pull/7561)) ; épingler `api-platform/core: ^4.x` et vérifier dès l'install ; parade si conflit de constraints : prévenir avant toute refonte.
- **Lexik JWT × Symfony 8** : vérifier la compatibilité à l'install ; parade : si blocage, route `/api/login` maison émettant un JWT (mêmes flux frontend).
- **Tailwind v4 + Vite** : utiliser `@tailwindcss/vite` (le piège PostCSS « tailwindcss directly as plugin » est documenté) — [discussion tailwindlabs #16201](https://github.com/tailwindlabs/tailwindcss/discussions/16201).
- **Volume de suppression** : suppressions faites phase par phase avec tests verts à chaque étape pour éviter la casse silencieuse.

## 9. Décisions à confirmer (recommandations par défaut)

1. **TypeScript** pour le frontend (recommandé) ?
2. **JWT stateless (Lexik)** plutôt que cookies de session (recommandé, chemin officiel API Platform) ?
3. **Champs de profil** : ajout `firstName`/`lastName` à `User` (recommandé) ou email seul ?
4. **Périmètre admin v1** : suspendre/réactiver + rôles + suppression (recommandé) ; « reset mot de passe par l'admin » en v2 ?
5. **Inscription** : redirection vers le login après succès (recommandé) plutôt qu'auto-login ?
6. **Composants UI** : primitives maison Tailwind + lucide (recommandé) plutôt que shadcn/ui ?

---

**Prochaine étape** : valider (ou amender) les décisions du §9, puis implémenter phase par phase (§7), avec commit et gates de qualité verts à chaque jalon.