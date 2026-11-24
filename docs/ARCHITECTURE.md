# Architecture — be-invoice

Phase 3 audit document. Reflects the repository exactly as it stands at `master` HEAD `f9021b3` (2026-11-24) — no aspirational content. See `docs/REPOSITORY_AUDIT.md` for the full inventory this is summarized from, and `projects/info/analysis/be-invoice.md` for the business-purpose finding this document assumes as context: **there is no invoicing domain logic in this repository today.**

## Module diagram (as it actually exists)

```
be-invoice/
├── app/
│   ├── Console/Kernel.php           Laravel default, unmodified
│   ├── Exceptions/Handler.php       Laravel default, unmodified
│   ├── Http/
│   │   ├── Controllers/Controller.php   empty base class, no subclasses
│   │   ├── Kernel.php                   Laravel's legacy (pre-11-style) HTTP kernel
│   │   └── Middleware/*                 7 files, all Laravel defaults, unmodified
│   ├── Models/User.php              only model in the application
│   └── Providers/*                  4 files, all Laravel defaults, unmodified
├── routes/
│   ├── api.php      1 route: GET /api/user (auth:sanctum) — framework default
│   ├── web.php       2 routes: GET / , GET /contact — both View::make() to static Blade
│   ├── channels.php  Laravel default, unmodified
│   └── console.php   Laravel default, unmodified
├── database/
│   ├── migrations/   4 files — all Laravel defaults (users, password_resets,
│   │                  failed_jobs, personal_access_tokens). No application table.
│   ├── factories/UserFactory.php    Laravel default
│   └── seeders/DatabaseSeeder.php   Laravel default
├── resources/
│   ├── views/pages/{home,contact}.blade.php   + shared layout/includes
│   └── js/{app.js,bootstrap.js}, css/app.css  Vite entry points, stock content
├── public/
│   ├── invoice.html                 556-line static demo page, NOT routed through
│   │                                 Laravel (unreachable via the app; only reachable
│   │                                 by requesting the literal file path) — part of
│   │                                 the vendored admin-theme's own demo-page set
│   └── assets/                      vendored third-party admin-dashboard theme
│                                     (CSS/SCSS/JS), added wholesale in one commit
└── tests/
    ├── Feature/ExampleTest.php      Laravel's generated stub (asserts 200 on /)
    └── Unit/ExampleTest.php         Laravel's generated stub (assertTrue(true))
```

No `app/Http/Resources/`, no `app/Services/`, no `app/Repositories/`, no `app/Jobs/`, no `app/Events/`, no `app/Listeners/` directories exist. There is nothing resembling a business/domain layer.

## Data model

One Eloquent model: `App\Models\User` (Laravel's default — `name`, `email`, `password`, `HasApiTokens`/`HasFactory`/`Notifiable` traits). No `Invoice`, `Customer`, `LineItem`, or any other domain model exists in code, migrations, or any unmerged branch.

## API contract

| Method | Path | Middleware | Handler | Notes |
|---|---|---|---|---|
| GET | `/api/user` | `auth:sanctum` | inline closure, `routes/api.php:17` | Laravel's Sanctum-scaffold default; returns the authenticated user |
| GET | `/` | `web` | inline closure → `View::make('pages.home')` | Renders the vendored admin-theme's home page |
| GET | `/contact` | `web` | inline closure → `View::make('pages.contact')` | Renders the vendored admin-theme's contact page |

No other endpoint exists. There is no OpenAPI/Swagger spec, no versioning scheme, no pagination/filtering convention to document, because there is no business API surface yet.

## Auth

Laravel Sanctum `v4.5.0` is installed and its migration (`personal_access_tokens`) is present, but the only guarded route is the framework-default `/api/user`. `config/sanctum.php` is the unmodified stock config (stateful domains from `SESSION_DOMAIN`/`APP_URL`, default `EnsureFrontendRequestsAreStateful` + `authenticate_session` middleware group). No token-issuance endpoint (login/register) exists anywhere in the routes — Sanctum is wired but there is no way to actually obtain a token through this application today.

## Database

`.env.example` declares `DB_CONNECTION=mysql` (host `127.0.0.1`, db `laravel`), but no application table/migration beyond Laravel's own 4 defaults exists. CI (`.github/workflows/ci.yml`) runs tests against an in-memory `sqlite` database instead, created fresh each run (`touch database/database.sqlite`).

## Config inventory

Standard Laravel `config/*.php` set (15 files: `app`, `auth`, `broadcasting`, `cache`, `cors`, `database`, `filesystems`, `hashing`, `logging`, `mail`, `queue`, `sanctum`, `services`, `session`, `view`) — all left at framework defaults, no custom config file added. Notable defaults, flagged for Phase 9 attention once real endpoints exist (not urgent today, since there is nothing behind them to protect):
- `config/cors.php`: `allowed_origins => ['*']`, `supports_credentials => false` — Laravel's own stock default, appropriate for a brand-new project, worth tightening once a real frontend origin is known.
- `.env.example`: `SESSION_DRIVER=file`, `CACHE_DRIVER=file` — node-local drivers; see the "multi-server" roadmap note in `docs/IMPROVEMENT_ROADMAP.md` for why these would need to move to `redis`/`database` before any horizontal scaling.

## Testing

Two files, both Laravel's own generated stubs: `tests/Feature/ExampleTest.php` (asserts the root route returns HTTP 200) and `tests/Unit/ExampleTest.php` (`assertTrue(true)`). Neither exercises any application-specific behavior, because none exists yet. CI (`.github/workflows/ci.yml`, workflow name "Laravel CI") runs `php artisan test` on every push/PR to `master` only (not `preview` — the repo has not yet adopted the 3-branch model; see `docs/REPOSITORY_AUDIT.md` §Git hygiene and Phase 4 notes).

## Security note carried from the audit (full detail in REPOSITORY_AUDIT.md)

A string matching Google's API-key format was found hardcoded in `public/assets/js/google-charts.js:7` (vendored third-party theme asset, part of the same bulk commit that added the `public/assets/` tree). Reported as a P0 finding per CLAUDE.md's secret-handling policy — value never reproduced in any document. See `docs/TECHNICAL_DEBT.md` and `docs/REPOSITORY_AUDIT.md` for full handling notes; removal from history is **Approval Gate 1** and is not performed in this phase.
