# Repository audit — be-invoice

Phase 3 (`auditor` role), run 2026-11-24 against `origin/master` HEAD `f9021b3` and `origin/feat/invoice-platform` HEAD `db82558` (unmerged, 1 commit ahead of its own fork point). Full command evidence is in the Phase 1–3 handoffs (`projects/info/state/handoff/{1,2,3}/be-invoice.md`); this document summarizes the findings themselves.

## 1. Full inventory

- **Manifests:** `composer.json` (10 direct deps: 3 runtime, 7 dev), `package.json` (5 dev-only deps: `axios`, `laravel-vite-plugin`, `lodash`, `postcss`, `vite`). Neither has ever had a lockfile committed (`composer.lock`/`package-lock.json` absent on every commit of `master`, confirmed via `git log --all --follow`).
- **PHPUnit:** `phpunit.xml` is Laravel's stock config (4 testsuites defined — `Unit`, `Feature` — env overrides for `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `CACHE_STORE=array`, `SESSION_DRIVER=array`, `QUEUE_CONNECTION=sync`). No `phpstan.neon`/`larastan` config exists.
- **`config/*.php`:** all 15 files present, all at framework defaults — see `docs/ARCHITECTURE.md` §Config inventory.
- **`routes/*.php`:** 4 files — `api.php` (1 route), `web.php` (2 routes + 1 dead commented-out route), `channels.php`/`console.php` (both untouched defaults).
- **`app/Models` / `Controllers` / `Resources` / `Providers`:** only the stock `User` model, the empty base `Controller`, no Resources directory, 4 stock Providers. No business code anywhere.
- **`database/migrations` / `factories` / `seeders`:** only the 4 Laravel-default migrations, the default `UserFactory`, the default (empty) `DatabaseSeeder`.
- **`tests/**`:** 2 files, both framework-generated stubs (see `docs/ARCHITECTURE.md` §Testing).
- **CI/CD:** `.github/workflows/ci.yml` (named "Laravel CI" internally) — single job, PHP-only, triggers on push/PR to `master`. `actions/checkout@v4`, `shivammathur/setup-php@v2` (PHP 8.5.12, sqlite extensions). No frontend (Vite) build/lint/test job exists. Targets `master`, not `preview` — the repo has not adopted the 3-branch model (standing decision 5); flagged for Phase 4.
- **Docker:** none. No Dockerfile, no `docker-compose.yml`, no Makefile, and — after re-verification — no `docker/` directory of any kind anywhere in git history on any branch (`git ls-files`/`git log --all -- docker` both return nothing). An earlier draft of this audit incorrectly stated `docker/php/`/`docker/nginx/` existed as empty tracked placeholders; that was wrong and is corrected here and in `projects/info/deps/be-invoice.md` / `DEPENDENCY_MAP.md`.
- **`docs/`:** did not exist before this phase; created now by this audit.
- **`.env.example`:** present, 40 lines, placeholder values only (`APP_KEY=` empty, `DB_PASSWORD=` empty, all `AWS_*`/`PUSHER_*` blank) — compliant with the Security policy's "placeholders only" requirement. `.gitignore` correctly excludes `.env` (verified present).
- **`.github/`:** only the one workflow; no issue templates, no PR template, no `dependabot.yml`, no `CODEOWNERS`.
- **Root community files:** none of `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`, `SECURITY.md`, `LICENSE`, `CHANGELOG.md`, `ACCESSIBILITY.md`, `SUPPORT.md` exist. `composer.json` self-declares `"license": "MIT"` but there is no `LICENSE` file and GitHub's own `licenseInfo` is `null` (confirmed via `gh repo view --json licenseInfo`).

## 2. Dependency analysis

Full tables in `projects/info/deps/be-invoice.md` (Phase 2). Summary:
- `laravel/framework` and `phpunit/phpunit` are each 1 major behind latest-stable; both already have open Renovate PRs (#40, #41 respectively).
- `axios` (npm) is pinned to npm's legacy `v0x` dist-tag line (`^0.35.0`) rather than the current `1.x` major — not vulnerable today, but a standing-decision-1 gap.
- `actions/checkout@v4` is 3 majors behind (`v7.0.1` latest); open Renovate PR #39 already proposes it. `shivammathur/setup-php@v2` is current.
- `composer audit --locked` and `npm audit`, run against freshly-generated (first-ever) lockfiles in disposable containers, both returned **zero vulnerabilities**.
- No unused dependency found in either manifest — even `lodash`/`axios` are referenced (by `resources/js/bootstrap.js`, Laravel's stock Vite bootstrap file).

## 3. Config review

- `config/cors.php`: stock Laravel defaults (`allowed_origins: ['*']`), appropriate for a project with no real frontend origin yet decided; revisit once one exists (Phase 9 territory, not urgent now).
- `config/sanctum.php`: stock, no stateful domains beyond `SESSION_DOMAIN`/`APP_URL` defaults.
- `config/hashing.php`: stock `bcrypt` driver, no custom hasher binding anywhere in `app/Providers` — no SHA1-vs-bcrypt ambiguity exists in this repo (that pitfall, documented in the `phase-3-audit-php` skill from a different repo, does not apply here; checked and ruled out, not skipped).
- `config/auth.php`: stock `web`/`api` guards, `api` guard driver is `sanctum` by Sanctum's own install default — no Passport-vs-Sanctum conflict (Passport is not installed at all in this repo; that pitfall also doesn't apply here).
- `.env.example` session/cache drivers are `file` (node-local) — a real blocker for multi-server deployment, noted as a roadmap item, not a current defect (nothing to scale yet).

## 4. Code quality findings

- `routes/web.php:16-18` — a dead commented-out `Route::get('/', ...)` left above the live route. Trivial, Phase 7 candidate.
- `composer.json` never renamed from the `laravel/laravel` skeleton defaults (`name`, `description` fields) — cosmetic, Phase 15 candidate.
- `README.md` is the unmodified stock Laravel framework README — says nothing about this specific project. Phase 15 candidate.
- No `app/Http/Resources`, no API versioning, no form-request validation classes — not "missing" so much as "nothing exists yet to need them."

## 5. Security scan

- `gitleaks dir . --redact --no-banner` (working tree only, this phase's scope) found one finding: `public/assets/js/google-charts.js:7`, rule `gcp-api-key`, a hardcoded string matching Google's API-key format assigned to a JS property `mapsApiKey`. **Value never reproduced in this or any document.** Provenance: introduced in commit `6437a70` (2022-09-09), part of the 286-file vendored admin-theme drop; the key is never referenced by any `.env*`, PHP config, or Blade view in this application — nothing in the app's own code depends on it, consistent with (not proof of) it being the theme vendor's own shared demo key. Reported per CLAUDE.md Security policy as a **found-and-reported, not self-cleared** item: Rasim should confirm whether this key is live/billable before any decision is made; removing it from history would be **Approval Gate 1** and is explicitly not attempted here.
- No other secret pattern matched. `APP_KEY` in `.env.example` is empty (placeholder), not a real key.
- No SQL built via string concatenation anywhere (no custom queries exist at all — only Eloquent's default `User` model usage).
- CORS/rate-limiting/auth config all reviewed above — all stock, no custom weakening found.

## 6. CI/CD status

Single PHP-only job, green by inspection (not independently re-run in Docker this phase, per Phase 3's docs-only scope) — triggers on `master` push/PR. No Docker build/test stage exists (none to call; Phase 5 builds the baseline). No frontend build/lint/test step. Targets the wrong branch under the 3-branch model (should target `preview` once Phase 4 builds it) — flagged, not fixed, per R1 (one agent, one phase).

## 7. Git hygiene

- 39 commits on `master`, clean, `git fsck --full --strict` exit 0 (Phase 1 evidence).
- 1 unmerged branch, `origin/feat/invoice-platform` (`db82558`, "Refactor application configuration and structure for invoice platform setup") — adds exactly `bootstrap/providers.php` + `composer.lock` (first-ever lockfile on any branch), deletes/rewrites the old Kernel-style bootstrap files to Laravel's newer convention. **Zero business/domain files on this branch either** — it is an infrastructure-modernization attempt, not feature work, despite its name. Its disposition (merge/rebase/close) is Phase 4's decision, not made here.
- 3 open Renovate PRs (#39 checkout, #40 laravel/framework, #41 phpunit) — all three map directly to confirmed version gaps above; likely safe to merge once Phase 6 independently verifies the same targets.
- No `renovate.json` committed — Renovate runs via the account-level GitHub App default preset (consistent with other repos in this program). Standing decision 14's "keep configured to policy" applies; no `dependabot.yml` needed alongside it.
- Repository is still on a single default branch (`master`); the 3-branch model (`preview`/`test`/`main`, standing decision 5) has not been built yet. This PR (branch `phase-3/audit-plan`) targets `master` for that reason, per the task's own instruction to note (not build) this as a Phase-4 finding.
- No `.idea/`/`.vscode/` committed (checked, clean).

## 8. Business-logic finding (headline — full evidence in `projects/info/analysis/be-invoice.md`)

Despite the repository's name and a commit literally titled "Customer & Invoice blade add," **no invoicing or customer business logic exists anywhere in this repository's history, on any branch.** The full evidentiary chain (commit-by-commit diff inspection, not inference from commit messages) is documented in `projects/info/analysis/be-invoice.md`. This finding governs the entire Phase 4-11 plan below: there is a stock, mostly-unmodified Laravel skeleton plus one vendored admin-theme UI drop to operate on, not a working invoicing system.
