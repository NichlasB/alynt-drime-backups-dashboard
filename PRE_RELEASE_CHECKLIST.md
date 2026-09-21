# Alynt Drime Backups Dashboard Pre-Release Checklist

Updated: 2026-09-21

Use this checklist to track release-candidate readiness for the `Alynt Drime Backups Dashboard` plugin. Mark a workflow complete only after current evidence has passed for the recorded candidate.

## Current Release Candidate

- Candidate version: `0.1.43`
- Previous published release: `v0.1.42`
- Candidate purpose: release the non-mutating V2.3 Schedule Rollback Preview dashboard-side dispatch/UI slice, action-history wording, Diagnostics audit labels, and support-safe action aggregates.
- Boundary: release-prep source/docs/package metadata only. This candidate does not deploy/update `control-sitesmanage`, create backups, restore, delete, clean up, change WPvivid/server-runner schedules, execute rollback, change credentials, store Drime API credentials, run arbitrary commands, or perform database/server actions.
- Current checklist note: rows updated on 2026-09-21 reflect the current `0.1.43` release candidate after the local DS3 pre-release pass and validation commands listed below.

## Prerequisites

- [x] Build tooling present: `package.json`, Node build script, PHPUnit, PHPCS/WPCS, Composer dev tooling, GitHub release workflow.
- [x] Observability present: Diagnostics tab, support-safe export/copy, redacted polling/action/source aggregates.
- [x] Updater compatibility present: GitHub Plugin URI and prior GitHub release/update flow established through previous dashboard releases.
- [x] Restore point/rollback baseline available through local Git history, current `v0.1.42` release/tag, and the user's fresh live GridPane backup from the prior release/deploy cycle. No live-site, database, deployment, or destructive action was performed in this pass.

## Current Feature Workflow Tracking

- [x] V2.3 preview-only schedule visibility implemented locally.
- [x] DS2 Feature Light Review completed. Result: small hardening/polish issues found and fixed.
- [x] DS2 Feature Bloat And Structure Phase 1 completed. Result: changed large files are existing cohesive architecture/test files; no safe feature-stage split forced.
- [x] DS2 UI/UX Review completed. Result: native admin copy/buttons/status presentation retained; schedule UI is preview-only and non-mutating.
- [x] DS2 Security Review completed. Result: schedule capability sanitizer now ignores unsupported schedule IDs and never enables apply/rollback.
- [x] V2.3 Schedule Rollback Preview dashboard-side dispatch/UI, action-history wording, Diagnostics audit labeling, and support-safe action aggregates are implemented locally in four commits after `v0.1.42`.

## Pre-Release Review Sequence

- [x] 01 Code Cleanup Review: rerun on 2026-09-21; no source TODO/FIXME/debug remnants found by targeted scans. Matches are intentional build-script console output and normal WordPress `wp_die()` permission/activation paths.
- [x] 02 File Structure Review: rerun for current `0.1.43` source on 2026-09-21. Runtime `includes/*.php` files are under the prior workflow bloat threshold; the rollback-preview slice uses focused traits instead of growing the dispatcher/repository monoliths.
- [x] 03 Error Handling Review: rollback-preview unavailable, missing-apply, not-ready, metadata-missing, metadata-invalid, metadata-expired, and unsupported-capability paths render explicit non-mutating feedback.
- [x] 04 WP Best Practices Review: WordPress APIs, translatable strings, nonces/capability gates, and existing admin patterns retained.
- [x] 05 Database Review: no schema/table migration introduced for `0.1.43`; existing remote-action storage records support-safe action context only.
- [x] 06 Performance Review: no scheduled poll broadening and no Drime/API browsing added; rollback-preview lookup is bounded to existing local action history.
- [x] 07 Edge Cases Review: rollback-preview remains hidden unless the latest client capabilities and a fresh successful Schedule Apply record support it; stale/missing metadata is rejected.
- [x] 07A Adversarial Test-Suite Review: focused tests and full PHPUnit baseline cover capability gating, dispatcher payloads, repository rollback-preview lookups, Diagnostics counts, and rendering.
- [x] 08 Uninstall Review: no new persistent table/option/schedule ownership added in this slice.
- [x] 09 I18N Review: user-facing strings remain wrapped with the correct `alynt-drime-backups-dashboard` text domain. POT header was bumped manually; `npm.cmd run pot` remains blocked because local `wp` CLI is unavailable.
- [x] 10 Accessibility Review: rollback-preview controls use existing admin form patterns, visible explanatory copy, nonce/capability gates, and no keyboard-hostile custom controls.
- [x] 11 Code Quality Review: PHPUnit, PHPCS, build, npm audit, Composer audit, whitespace checks, release metadata review, and source-size inventory passed for current local candidate.
- [x] 12 Documentation Review: checklist, readme, README, changelog, protocol/threat-model/design docs, and implementation-plan wording describe rollback preview as non-mutating and separately gated.
- [x] 13 Security Audit: targeted scans found no new dangerous runtime PHP patterns; request/DB usage remains sanitized/prepared; rollback-preview requires signed action dispatch and client-side support, and dashboard does not receive Drime credentials.

## Release Validation

- [x] Main plugin PHP syntax check passed.
- [x] Focused remote-action repository tests passed: 11 tests, 89 assertions.
- [x] Focused Diagnostics tests passed: 8 tests, 63 assertions.
- [x] PHPUnit passed: 215 tests, 1085 assertions, 2 expected skips.
- [x] PHPCS passed across 109 files.
- [x] Build passed: `npm.cmd run build`.
- [x] npm audit passed: 0 vulnerabilities at moderate threshold.
- [x] Composer audit passed via local `php .\composer.phar audit`: no security vulnerability advisories found.
- [x] `git diff --check` passed.
- [x] Translation-template coverage checked for rollback-preview source strings; runtime strings remain wrapped, but generated POT references were not regenerated because WP-CLI is unavailable.
- [ ] `npm.cmd run pot` blocked: `wp` CLI is not on PATH in this environment. Run `wp i18n make-pot` on a machine with WP-CLI before or during release packaging if generated POT provenance is required.
- [ ] Release ZIP audit not yet run for `0.1.43`.
- [ ] GitHub release not yet created.
- [ ] Updater install/update smoke verification not yet run for `0.1.43`.
- [ ] Live dashboard deployment/update on `control-sitesmanage` not yet performed.

## Open Items

- [ ] Commit `0.1.43` release-prep metadata/checklist changes.
- [ ] Push local `0.1.43` commits to `origin/master` and verify CI.
- [ ] Tag/publish `v0.1.43` and verify release asset packaging.
- [ ] Run release ZIP audit for `0.1.43`.
- [ ] Deploy/update dashboard plugin on `control-sitesmanage` only after live-site approval.
