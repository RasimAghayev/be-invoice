# Improvement roadmap — be-invoice

Phase 3 audit document: the Phase 4–11 (and 15) plan for this repo, submitted for **Gate 10** approval. Built on the findings in `docs/REPOSITORY_AUDIT.md`, `docs/TECHNICAL_DEBT.md`, and `projects/info/analysis/be-invoice.md` — in particular the headline finding that **no invoicing business logic exists anywhere in this repository today**. This roadmap plans work on the skeleton as it actually stands; it does not propose, design, or assume approval for building the invoicing domain itself — that is Phase 8B's job, explicitly gated by **Gate 12**, not pre-decided here.

## Phase 4 — git-steward
- Triage the 3 open Renovate PRs (#39 `actions/checkout`, #40 `laravel/framework`, #41 `phpunit/phpunit`) — all three map to confirmed gaps in `docs/REPOSITORY_AUDIT.md` §2; likely mergeable once Phase 6 independently reaches the same targets, or supersedable by Phase 6's own commits.
- Decide the fate of `origin/feat/invoice-platform` (rebase onto the post-Phase-6 state, or close as superseded — Phase 6 will reach the same `bootstrap/providers.php` convention via its own upgrade path regardless).
- Build the 3-branch model (`preview`/`test`/`main`) — does not exist yet (repo is single-branch `master`). Converting `master` → `main` as the default branch is **Gate 4**; park and batch with any other pending default-branch changes in this program rather than acting unilaterally.
- Re-target `.github/workflows/ci.yml`'s triggers from `master` to `preview` once the 3-branch model exists.
- Confirm Renovate is configured to standing-decision-14 policy (weekly, correct target branch once `preview` exists) — no `dependabot.yml` needed alongside it.
- Hold the TD-01 secret finding for Rasim's confirmation before any history action; do not action Gate 1 without explicit sign-off.
- No open GitHub issues or Discussions exist to triage (confirmed via `gh issue list`/`gh api` during Phase 1/2); no Projects board exists either — nothing to reconcile under standing decision 16.

## Phase 5 — container-engineer
- Build the Docker baseline **from scratch** (Dockerfile, `.dockerignore`, `docker-compose.yml`, Makefile) — there is nothing existing to improve (TD-02). Base images: PHP (latest-stable per decision 1 — `8.6` cycle, confirmed in `deps/be-invoice.md`), a thin Nginx or `php:8.6-fpm` + Nginx pairing consistent with other Laravel repos already through this phase in the program.
- `docs/BASELINE.md`: the honest baseline is "2 framework-stub tests, no real application behavior" — record this plainly, don't inflate it.
- `.env.example` already exists and is compliant (placeholders only) — carry forward, add any new Docker-specific vars (`DB_HOST=mysql`, etc.) as needed.

## Phase 6 — upgrader
- **Step 0:** generate `composer.lock` and `package-lock.json` for the first time (not a regeneration — see TD-03).
- PHP floor `^8.5.12` → `^8.6` (latest-stable cycle, released 2026-11-19).
- `laravel/framework` `^12.0` → `^13.0` (targets `v13.44.1`).
- `phpunit/phpunit` (dev) `^12.0` → `^13.0` (targets `13.4.8`).
- `spatie/laravel-ignition` (dev) `^2.12` → `^2.13` (minor).
- `axios` (npm dev) off the legacy `v0x` line onto `^1.x` (targets `1.21.1`) — this is a major-version jump in practice even though it reads as "up," since `0.x`→`1.x` crossed Axios's own breaking-change boundary years ago; treat with the same care as any other major bump (build+test in Docker, check for breaking API changes) despite the small diff in this repo's own usage surface.
- `actions/checkout@v4` → `@v7.0.1`, then SHA-pin both actions.
- **Decision-17 check:** read Laravel 13's and PHP 8.6's changelogs for anything that supersedes this repo's own code. Given how little custom code exists (one closure-based controller-free route set, no custom middleware, no custom providers), expected yield is low — but the check is performed, not skipped, and any genuinely-applicable item (e.g., a new built-in replacing something hand-rolled) is adopted in the same pass or handed to Phase 7 as a named follow-up, logged in `CHANGELOG.md` either way.
- Merge/close the 3 Renovate PRs once this phase's own commits reach the same or newer targets.

## Phase 7 — cleaner
- Remove the dead commented-out route (`routes/web.php:16-18`, TD-15).
- Re-shape to whatever new conventions Phase 6's Laravel-13 upgrade brings (e.g., if `bootstrap/providers.php`-style registration is adopted, retire the old `app/Http/Kernel.php`/`app/Console/Kernel.php` pattern the same way `feat/invoice-platform` attempted).
- Expected to be a short phase — there is very little dead code to find in a repository this close to a stock skeleton.

## Phase 8 — architect
- Recommend treating as **largely `n/a` pending Phase 8B**: DRY/SOLID/DDD/Clean-Hexagonal/etc. need existing structure to meaningfully apply to, and today there is a stock skeleton with no business logic to restructure. Forcing an ADR against an empty skeleton would not meet decision 7's "solves a real, present problem" bar.
- If Rasim prefers, this phase can instead focus on establishing the *target* architecture shell (e.g., where a future `Invoice` module would live — `app/Domain/Invoicing/` or similar) as a documented convention without building any logic — a judgment call left to the Phase 8 agent and Rasim at that time, not pre-decided here.

## Phase 8B — feature-enhancer (the pivotal phase for this repo)
- This is realistically where be-invoice's actual reason for existing would first get built, if desired. Candidate shapes (not proposed or pre-approved here — Phase 8B's own job, under **Gate 12**): a minimal `Customer` + `Invoice` CRUD API; PDF invoice generation; a dashboard list view reusing the already-installed admin theme assets.
- Explicitly not designed, scoped, or assumed in this roadmap. Phase 8B must independently produce 2–3 candidates with business value, acceptance criteria, implementation strategy, and test plan, then stop at Gate 12 before building anything — same as every other repo in this program.

## Phase 9 — hardener
- Minimal scope until Phase 8B lands — today's only hardenable surface is the stock Sanctum `/api/user` endpoint and Laravel's default CORS/rate-limiting config (`docs/ARCHITECTURE.md` §Config inventory). Revisit CORS's `allowed_origins: ['*']` once a real frontend origin exists.
- Externalize `SESSION_DRIVER`/`CACHE_DRIVER` from `file` to `redis`/`database` as part of this phase's standard "production-ready config" pass — this also happens to be the one concrete prerequisite for the multi-server question below, so it's worth doing even before Phase 8B adds real load to distribute.

## Phase 10 — CI/CD
- Rebuild `.github/workflows/ci.yml` once Phase 5's Docker baseline exists — add a Docker build/test stage (there is none today, since there was no Dockerfile to build from), add a frontend (Vite) build/lint job (none exists today), retarget to `preview`, SHA-pin actions (carried from Phase 6 if not already done there).

## Phase 11 — tester
- Characterize the 2 existing framework-stub tests honestly (they test nothing application-specific). Real test-writing scope is bounded by whatever Phase 8B adds — this phase cannot invent meaningful business-logic tests against a skeleton with no business logic.

## Phase 15 — documenter
- Full scope: this repo has the most community-standard-file ground to cover of any repo audited so far in this program (TD-10). `README.md` needs a real rewrite (TD-14), `composer.json` needs its `name`/`description` corrected (TD-13), `LICENSE` needs to be materialized to match the declared MIT license (TD-08) or the declaration corrected to match reality — Rasim's call, flagged not decided here.

---

## Roadmap item (a): latest-stable targets (standing decision 1)

Already itemized above, phase by phase, drawn directly from the live-registry evidence in `projects/info/deps/be-invoice.md` (Phase 2). No blocker entries are needed in `docs/TECHNICAL_DEBT.md` at this time — every dependency in this repo has a clean latest-stable path, unlike (for comparison, elsewhere in this program) `taskStarEx/fe`'s `vue-router` 4→5 / Vuex conflict.

## Roadmap item (b): could this repo sensibly support a distributed/multi-server deployment?

Answered honestly as a **forward-looking design note, not a current-state assessment** — there is no workload to distribute yet:

- **Framework-level answer:** yes, in principle. Laravel 12/13's own defaults (stateless HTTP handling, pluggable session/cache/queue drivers, horizontally-scalable queue workers) support the standard pattern — N stateless app-server replicas behind a load balancer, session/cache externalized to Redis, one shared database, queue workers scaled independently of web workers. This is a generic capability of the framework and applies the same way it does to every other Laravel repo already audited in this program; it is not something specific to be-invoice's (currently nonexistent) business logic.
- **Repo-level answer today:** the question doesn't yet have an application to apply to. The one concrete, present blocker — not hypothetical — is `.env.example`'s `SESSION_DRIVER=file`/`CACHE_DRIVER=file`, which are node-local and would misbehave under any 2+-node deployment (a session written on node A wouldn't be visible on node B). This is a one-line config change, planned above under Phase 9, independent of whether Phase 8B ever adds real invoicing logic.
- **Recommendation:** treat multi-server readiness as "the generic Laravel pattern applies, and the one real blocker (file-based session/cache) is already on the Phase 9 plan" — not as a reason to add scope or infrastructure now. No load balancer, no Redis, no multi-node deployment is proposed or built in this program's current phases for this repo; this is recorded purely as a design note for whenever (if ever) a real deployment-scale decision is made.

## Gate 10 notice

This document, together with `docs/ARCHITECTURE.md`, `docs/REPOSITORY_AUDIT.md`, `docs/TECHNICAL_DEBT.md`, and `projects/info/analysis/be-invoice.md`, constitutes the Phase 3 plan for `be-invoice` submitted for **Gate 10** approval. No Phase 4 work begins on this repo until Rasim explicitly approves this plan. The repo is marked `needs-approval` in `projects/info/state/PHASES.md` and logged in `projects/info/state/APPROVALS.md`.
