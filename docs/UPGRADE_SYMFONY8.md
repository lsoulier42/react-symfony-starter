# Plan de migration : Symfony 7.4 / PHP 8.4 → Symfony 8.x / PHP 8.5

## Contexte / cibles

- **Cible retenue : Symfony 8.1 / PHP 8.5** (voir note miroir ci-dessous).
- **Date** : août 2026.
- **Note miroir (environnement sandbox)** : le miroir Composer disponible ne propose
  Symfony que jusqu'à `8.2.x-dev` ; la dernière **stable** est **8.1.x** (`v8.1.4`).
  `8.5.*` / `8.2.*` ne résolvent pas (absents ou dev-only). On cible donc `8.1.*`.
- **PHP 8.5** : image `php:8.5-fpm` construite avec succès ; PHP 8.5 supporté par Symfony 8.1.
- **Risque code applicatif** : faible. Le point dur rencontré est l'usage de
  `Symfony\Component\Routing\Annotation\Route` (classe **supprimée en Symfony 8**) →
  remplacé par `Symfony\Component\Routing\Attribute\Route`.
- **Risque réel** : alignement des versions des bundles tiers (monolog-bundle needed ^4.0).

## Étapes

### 1. Version de PHP
- `composer.json` : `"php": "^8.4"` → `"^8.5"` (ou `"^8.4 || ^8.5"` pour garder de la souplesse en dev).
- `docker/php/Dockerfile` : `FROM php:8.4-fpm` → `FROM php:8.5-fpm`.
- Vérifier que `install-php-extensions` (latest) et les extensions (`xdebug pgsql pdo_pgsql intl zip apcu mbstring sodium`) existent pour 8.5 (oui, Xdebug ≥ 3.4).

### 2. Paquets Symfony
- Toutes les contraintes `symfony/*` `7.4.*` → `8.5.*` (dont `framework-bundle`, `security-bundle`, `doctrine-messenger`, `asset-mapper`, etc.).
- `composer.json` → `extra.symfony.require` : `7.4.*` → `8.5.*`.
- Dev : `symfony/phpunit-bridge`, `symfony/web-profiler-bundle`, `symfony/debug-bundle`, `symfony/stopwatch` → `8.5.*`.
- `symfony/flex` : garder `^2` (compatible 8) ou monter en dernière `^2.x`.

### 3. Compatibilité des dépendances tierces (point d'attention)
- `babdev/pagerfanta-bundle ^4.4` + `pagerfanta/pagerfanta ^4.6` : vérifier le support Symfony 8 → probablement `babdev/pagerfanta-bundle ^5` (+ core pagerfanta compatible).
- `symfony/ux-stimulus-bundle ^2.18` : vérifier Symfony 8 → possible ligne UX 3.x. Config asset-mapper inchangée.
- `symfony/monolog-bundle ^3.11` : vérifier Symfony 8 → v4 si nécessaire.
- `doctrine/orm ^3.6`, `doctrine/dbal ^4.4`, `doctrine/doctrine-bundle ^3.2` : déjà prêts pour 8, juste monter les mineures.
- `phpunit/phpunit ^11` → `^12` (support PHP 8.5), et adapter `symfony/phpunit-bridge`.
- `phpstan/phpstan* ^2.0` : monter `phpstan-symfony`/`phpstan-doctrine` si besoin des stubs Symfony 8. Niveau 6 conservé.
- `squizlabs/php_codesniffer ^3.7` : OK.

### 4. Résolution des dépendances
- `make composer-update` (lance `composer update -W`). En cas de conflit majeur, supprimer `composer.lock` + `vendor/` pour une résolution propre.
- **Laisser Flex fusionner les recettes** : il peut réécrire des fichiers `config/`. **Revoir chaque diff** (point critique).

### 5. Revue code & config (Symfony 8 / PHP 8.5)
- Lancer tests + PHPStan (niveau 6) + phpcs ; corriger les nouvelles `E_DEPRECATED` / erreurs d'analyse.
- Revue `config/packages/*.yaml` : la config actuelle utilise des fonctionnalités stables, mais vérifier les deprecations type recette (`web_profiler.collect_serializer_data`, `validator.email_validation_mode: html5`, `csrf` stateless, `uid` UUIDv7).
- `.env` : `serverVersion=16` vs image postgres `18.2` → mettre `18` ou supprimer (DBAL 4 auto-détecte).
- Confirmer l'existence des commandes `bin/console` (`importmap:install`, `asset-map:compile`).

### 6. Docker / infra
- Rebuild (`make install` / `docker compose build`).
- Vérifier supervisor (workers messenger), xdebug, healthchecks sur 8.5.

### 7. Vérification
- `make install` → migrations → fixtures → `http://localhost:8081` → profiler.
- `php bin/phpunit`, `vendor/bin/phpstan analyse`, `vendor/bin/phpcs`.
- Surveiller le canal `deprecation` Monolog et les notices PHP 8.5.

### 8. Documentation
- `README.md` / `.env` : nom de la base PostgreSQL unifié en `symfony` (suppression du nom legacy `symfony6-docker`).
- `AGENTS.md` : mettre à jour les versions de la stack.
- Commit propre + tag de l'upgrade.

## Résumé des risques
| Risque | Niveau |
|---|---|
| Code applicatif (APIs dépréciées) | Faible |
| Alignement bundles tiers (Pagerfanta/Stimulus/Monolog/PHPUnit) | Moyen |
| Fusions de recettes Flex (réécriture `config/`) | Moyen |
| Deprecations langage PHP 8.5 | Faible (Symfony 8.5 clean ; risque surtout tiers) |

## Suivi d'exécution

- [x] Étape 1 : PHP `^8.5` (composer.json) + `FROM php:8.5-fpm` (Dockerfile) — image build OK
- [x] Étape 2 : `symfony/*` → `8.1.*`, `extra.symfony.require` → `8.1.*` (retarget depuis 8.5/8.2 : miroir caps à stable 8.1)
- [x] Étape 3 : `symfony/monolog-bundle` `^3.0` → `^4.0` (requis, v3.x plafonne à Symfony 7) ; `babdev/pagerfanta-bundle` (^4.6 supporte ^8.0) et `symfony/ux-stimulus-bundle` (^2.36 supporte 8) OK sans bump ; `phpunit/phpunit` `^11` → `^12`
- [x] Étape 4 : `composer update -W` résolu (Symfony v8.1.4, monolog-bundle v4.0.2) ; recettes Flex : mises à jour disponibles (voir Étape 4 bis)
- [x] Étape 5 : `cache:clear`/`importmap:install`/`assets:install` OK ; PHPStan niveau 6 **vert** ; correction `Route` annotation + typage generics/iterable ; phpcs : restent des erreurs **préexistantes** (fichiers Symfony standards : `config/preload.php`, `config/reference.php`, `public/index.php`, `tests/bootstrap.php`, `src/Trait/Timestampable.php` CRLF)
- [ ] Étape 6 : rebuild Docker complet (`make install`) à valider en runtime
- [ ] Étape 7 : vérification runtime (migrations, fixtures, app sur :8081)
- [ ] Étape 8 : README / AGENTS.md mis à jour (faits) ; commit + tag

### Étape 4 bis — recettes Flex (recommandée, à revue)

`composer recipes` liste de nombreuses recettes avec « update available »
(ex. `symfony/framework-bundle`, `security-bundle`, `monolog-bundle`, `web-profiler-bundle`,
`twig-bundle`, `validator`, `translation`, `routing`, `asset-mapper`, `mailer`, `messenger`,
`uid`, `console`, `form`). L'app démarre déjà avec la config 7.4, mais pour figer le starter
kit sur les recettes 8.1 il faut lancer (idéalement en interactif, hors container non-TTY) :

```bash
make connect
composer recipes:update symfony/framework-bundle   # + autres paquets
```

**Attention** : `recipes:update` peut réécrire `config/` (ex. `security.yaml`) ; à faire
avec relecture des diffs. Non bloquant pour le fonctionnement.
