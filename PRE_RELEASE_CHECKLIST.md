# Alynt Drime Backups Dashboard Pre-Release Checklist

Updated: 2026-09-20

Use this checklist to track release-candidate readiness for the `Alynt Drime Backups Dashboard` plugin. Mark a workflow complete only after current evidence has passed for the recorded candidate.

## Current Release Candidate

- Candidate version: `0.1.40` plus local unreleased targeted pre-release structure cleanup.
- Previous published release: `v0.1.40`
- Candidate purpose: targeted DS3 pre-release subset for remote-action history UI changes and source-helper file-structure cleanup.
- Boundary: local source/test/checklist changes only. No live-site change, deployment, release, backup creation, restore, cleanup, settings, credential, Drime-token, database, or remote action is included.
- Current checklist note: rows explicitly updated on 2026-09-20 reflect the current targeted pre-release subset; the full pre-release suite should still be rerun before the next public release package if a release is prepared.

## Prerequisites

- [x] Build tooling present: `package.json`, Node build script, PHPUnit, PHPCS/WPCS, Composer dev tooling, GitHub release workflow.
- [x] Observability present: Diagnostics tab, support-safe export/copy, redacted polling/action/source aggregates.
- [x] Updater compatibility present: GitHub Plugin URI and prior GitHub release/update flow established through previous dashboard releases.
- [x] Restore point/rollback baseline available through local Git history. No live-site, database, deployment, or destructive action was performed in this pass.

## Current Feature Workflow Tracking

- [x] V2.3 preview-only schedule visibility implemented locally.
- [x] DS2 Feature Light Review completed. Result: small hardening/polish issues found and fixed.
- [x] DS2 Feature Bloat And Structure Phase 1 completed. Result: changed large files are existing cohesive architecture/test files; no safe feature-stage split forced.
- [x] DS2 UI/UX Review completed. Result: native admin copy/buttons/status presentation retained; schedule UI is preview-only and non-mutating.
- [x] DS2 Security Review completed. Result: schedule capability sanitizer now ignores unsupported schedule IDs and never enables apply/rollback.
- [x] Remote-action history filtering and compact details UI are committed and pushed on `master`; current local work is a non-behavioral structure cleanup discovered by the pre-release subset.

## Pre-Release Review Sequence

- [x] 01 Code Cleanup Review: rerun on 2026-09-20; no source TODO/FIXME/debug remnants found by targeted scans. Only documentation/checklist text and intentional build-script console output matched.
- [x] 02 File Structure Review: rerun for current `0.1.40` source on 2026-09-20. Runtime source bloat was reduced by extracting backup-source compact row helpers and operator detail helpers into dedicated traits. PHP/CSS/JS runtime source files are under workflow thresholds. Remaining over-threshold files are test files/test bootstrap only and are deferred as lower-risk test-suite organization work. Evidence: `npm.cmd test`, `npm.cmd run lint`, `npm.cmd run build`, `git diff --check`, and changed-file PHP syntax checks passed.
- [x] 03 Error Handling Review: status, schedule-capability, and unavailable-capability paths render explicit non-mutating feedback.
- [x] 04 WP Best Practices Review: WordPress APIs, translatable strings, nonces/capability gates, and existing admin patterns retained.
- [x] 05 Database Review: no schema/table migration introduced for V2.3; existing snapshot storage handles additive sanitized payload fields.
- [x] 06 Performance Review: no new remote calls, loops over bounded schedule arrays only, Diagnostics counts aggregate sanitized snapshot data.
- [x] 07 Edge Cases Review: targeted review rerun for remote-action history filters/details and backup-source helper split; existing empty-filter state, allowlisted GET filters, escaped output, and non-mutating details disclosure remain intact.
- [x] 07A Adversarial Test-Suite Review: targeted and full PHPUnit baselines rerun. Existing coverage protects filtered history rendering, schedule detail compact rendering, and backup-source evidence rendering; no new behavioral regression tests were required for the mechanical trait split.
- [x] 08 Uninstall Review: no new persistent table/option/schedule ownership added in this slice.
- [x] 09 I18N Review: targeted review rerun; moved user-facing source strings remain wrapped with the correct `alynt-drime-backups-dashboard` text domain. `npm.cmd run pot` remains blocked because local `wp` CLI is unavailable.
- [x] 10 Accessibility Review: targeted review rerun for remote-action history filters/details; filters have screen-reader labels, table has a caption and scoped headers, and details disclosure uses native `<details>/<summary>` keyboard behavior.
- [x] 11 Code Quality Review: PHPUnit, PHPCS, build, npm audit, whitespace checks, and source-size inventory passed for current local candidate.
- [x] 12 Documentation Review: checklist updated for the current targeted pre-release subset. No public docs changed because the local structure split does not alter user/admin behavior.
- [x] 13 Security Audit: targeted scans found no new dangerous runtime PHP patterns; request/DB usage remains sanitized/prepared; dashboard does not receive Drime credentials. New history filters are read-only GET display filters with sanitize/allowlist handling.

## Release Validation

- [x] Changed-file PHP syntax checks passed for the split backup-source helper traits.
- [x] Focused backup-source evidence tests passed: 5 tests, 49 assertions.
- [x] Focused schedule-management tests passed: 5 tests, 33 assertions.
- [x] PHPUnit passed: 203 tests, 1005 assertions, 2 expected skips.
- [x] PHPCS passed across 106 files.
- [x] Build passed: `npm.cmd run build`.
- [x] npm audit passed: 0 vulnerabilities at moderate threshold.
- [ ] Composer audit blocked: `composer` is not on PATH in this environment.
- [x] `git diff --check` passed.
- [x] Translation-template coverage checked for moved source-helper strings; runtime strings remain wrapped, but generated POT references were not regenerated because WP-CLI is unavailable.
- [ ] `npm.cmd run pot` blocked: `wp` CLI is not on PATH in this environment. Run `wp i18n make-pot` on a machine with WP-CLI before or during release packaging if generated POT provenance is required.
- [ ] Release ZIP audit not yet run for a post-`0.1.40` candidate.
- [ ] GitHub release not yet created.
- [ ] Updater install/update smoke verification not yet run for a post-`0.1.40` candidate.
- [ ] Live dashboard deployment/update on `control-sitesmanage` not yet performed.

## Open Items

- [ ] Commit current targeted DS3 structure/checklist changes.
- [ ] Make WP-CLI available and rerun `npm.cmd run pot`, or intentionally accept stale POT source references for a non-release local cleanup commit.
- [ ] Run release-package creation and ZIP audit after approval.
- [ ] Push/tag/publish a post-`0.1.40` release only after release approval.
- [ ] Deploy/update dashboard plugin on `control-sitesmanage` only after live-site approval.
