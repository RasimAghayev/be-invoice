# Technical debt — be-invoice

Phase 3 audit document. Items classified P0–P3 per the program's standing severity convention. Each item notes which future phase remediates it — nothing here is fixed in Phase 3 (docs/analysis only, per R1).

## P0 — Secret found

| ID | Location | Issue | Remediation phase |
|---|---|---|---|
| TD-01 | `public/assets/js/google-charts.js:7` | Hardcoded string matching Google's API-key format (`gitleaks` rule `gcp-api-key`), assigned to JS property `mapsApiKey`. Value never reproduced in any document per CLAUDE.md Security policy. Introduced 2022-09-09 (`6437a70`) as part of a vendored third-party admin-theme drop; never referenced by this app's own config — consistent with (not proof of) a theme-vendor shared demo key. **Needs Rasim's confirmation on rotation before any action.** | Reported now (Phase 3). Any history rewrite to remove it is **Approval Gate 1** — out of scope for every phase until explicitly approved. |

## P1 — Structural ("doesn't exist yet", not "needs improving")

| ID | Location | Issue | Remediation phase |
|---|---|---|---|
| TD-02 | repo root | No Docker baseline at all: no Dockerfile, no `docker-compose.yml`, no Makefile, no `docker/` directory of any kind (re-verified via `git ls-files`/`git log --all`, zero hits on any branch — corrects an earlier erroneous claim in this program's dependency docs that empty placeholder directories existed). | Phase 5, built from scratch. |
| TD-03 | `composer.lock`, `package-lock.json` | Neither has ever been committed, on any branch — builds are not reproducible across time. | Phase 6, step 0 (generate both before any version decision). |
| TD-04 | `tests/` | Only Laravel's own generated stub tests exist; no application-specific test coverage (none is possible yet — there's no application-specific behavior). | Phase 11, scope bounded by whatever Phase 8B (if approved) adds. |
| TD-05 | `laravel/framework`, `phpunit/phpunit` | Each 1 major behind latest-stable (already tracked by open Renovate PRs #40/#41). | Phase 6. |
| TD-06 | `package.json` | `axios ^0.35.0` pinned to npm's legacy `v0x` dist-tag line, not the current `1.x` major. | Phase 6. |
| TD-07 | `.github/workflows/ci.yml` | `actions/checkout@v4`, 3 majors behind (`v7.0.1`; open Renovate PR #39); neither action is SHA-pinned; workflow triggers on `master` instead of `preview` (3-branch model not yet adopted). | Phase 4 (branch retarget) / Phase 6 or 10 (version bump + SHA-pin). |
| TD-08 | repo root | `LICENSE` file does not exist; `composer.json` self-declares `"license": "MIT"` but GitHub's own `licenseInfo` is `null` — the declared license was never materialized. | Phase 15. |
| TD-09 | `origin/feat/invoice-platform` | Unmerged branch (1 commit, `db82558`), 5 days stale relative to `master`'s latest commit, carries a Laravel-11+-style bootstrap-convention migration (`bootstrap/providers.php`) that Phase 6/7 would otherwise redo independently. | Phase 4 decides merge/rebase/close. |

## P2 — Housekeeping

| ID | Location | Issue | Remediation phase |
|---|---|---|---|
| TD-10 | repo root | No community-standard files exist: `CODE_OF_CONDUCT.md`, `CONTRIBUTING.md`, `SECURITY.md`, `CODEOWNERS`, issue/PR templates, `CHANGELOG.md`, `ACCESSIBILITY.md`, `SUPPORT.md`. | Phase 15, full scope. |
| TD-11 | `.github/` | No `renovate.json` committed (runs via account-level GitHub App default preset) — not itself a defect, but should be explicitly verified/aligned to standing decision 14 rather than assumed. | Phase 4. |
| TD-12 | repo root | `docs/` did not exist before this phase (created now). | Closed by this phase. |

## P3 — Cosmetic

| ID | Location | Issue | Remediation phase |
|---|---|---|---|
| TD-13 | `composer.json:2,4` | `name`/`description` fields still read the unmodified `laravel/laravel` skeleton defaults. | Phase 7 or 15. |
| TD-14 | `README.md` | Unmodified stock Laravel framework README — describes nothing about this specific project. | Phase 15. |
| TD-15 | `routes/web.php:16-18` | Dead commented-out default route left above the live route. | Phase 7. |
| TD-16 | GitHub repo metadata | `description` field empty (`gh repo view`). | Phase 15. |

## Remediation roadmap mapping

See `docs/IMPROVEMENT_ROADMAP.md` for the phase-by-phase plan. No burndown target is set for "build the invoicing domain" — that is explicitly Phase 8B territory, gated by **Gate 12**, and is not assumed, scheduled, or pre-designed by this audit.

## Standing-decision-1 blocker tracking

No blocker entries are needed at this time — no dependency in this repo requires an exception to "latest stable always" (unlike, e.g., `taskStarEx/fe`'s `vue-router` 4→5 / Vuex-Pinia conflict documented elsewhere in this program). If Phase 6 discovers one, it is added here then, re-attempted every Dependabot/Renovate cycle per the strengthened decision-1 wording, and escalated after 3 consecutive unresolved cycles.
