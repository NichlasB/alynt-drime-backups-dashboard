# Alynt Drime Backups Dashboard Pre-Release Checklist

Updated: 2026-09-22

Use this checklist to track release-candidate readiness for the `Alynt Drime Backups Dashboard` plugin. Mark a workflow complete only after current evidence has passed for the recorded candidate.

## Current Release Candidate

- Candidate version: `0.1.44`
- Previous published release: `v0.1.43`
- Candidate purpose: release the rollback-preview readiness polish and support-safe Diagnostics aggregate counts added after `v0.1.43`.
- Boundary: release-prep source/docs/package metadata only. This candidate does not deploy/update `control-sitesmanage`, create backups, restore, delete, clean up, change WPvivid/server-runner schedules, execute rollback, change credentials, store Drime API credentials, run arbitrary commands, or perform database/server actions.
- Current checklist note: rows updated on 2026-09-22 reflect the current `0.1.44` release candidate after the local release-prep validation commands listed below. The full DS3 pre-release workflow is intentionally not rerun here because the toolkit marks it as not applicable to packaging/release-publication-only passes.

## Prerequisites

- [x] Build tooling present: `package.json`, Node build script, PHPUnit, PHPCS/WPCS, Composer dev tooling, GitHub release workflow.
- [x] Observability present: Diagnostics tab, support-safe export/copy, redacted polling/action/source aggregates.
- [x] Updater compatibility present: GitHub Plugin URI and prior GitHub release/update flow established through previous dashboard releases.
- [x] Restore point/rollback baseline available through local Git history and current `v0.1.43` release/tag. No live-site, database, deployment, or destructive action was performed in this pass.

## Current Feature Workflow Tracking

- [x] V2.3 preview-only schedule visibility implemented locally.
- [x] DS2 Feature Light Review completed. Result: small hardening/polish issues found and fixed.
- [x] DS2 Feature Bloat And Structure Phase 1 completed. Result: changed large files are existing cohesive architecture/test files; no safe feature-stage split forced.
- [x] DS2 UI/UX Review completed. Result: native admin copy/buttons/status presentation retained; schedule UI is preview-only and non-mutating.
- [x] DS2 Security Review completed. Result: schedule capability sanitizer now ignores unsupported schedule IDs and never enables apply/rollback.
- [x] Rollback-preview readiness and Diagnostics support-count polish are implemented locally in two commits after `v0.1.43`.

## Pre-Release Review Sequence

- [x] 01 Code Cleanup Review: rerun on 2026-09-21; no source TODO/FIXME/debug remnants found by targeted scans. Matches are intentional build-script console output and normal WordPress `wp_die()` permission/activation paths.
- [x] 02 File Structure Review: prior structure pass remains current for this small patch. The `0.1.44` changes extend existing focused Site Detail and Diagnostics traits without introducing new monoliths.
- [x] 03 Error Handling Review: rollback-preview unavailable, missing-apply, not-ready, metadata-missing, metadata-invalid, metadata-expired, and unsupported-capability paths render explicit non-mutating feedback.
- [x] 04 WP Best Practices Review: WordPress APIs, translatable strings, nonces/capability gates, and existing admin patterns retained.
- [x] 05 Database Review: no schema/table migration introduced for `0.1.44`; existing local records are read for support-safe readiness/count display only.
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
- [x] Focused Site Detail polling-state rendering tests passed: 30 tests, 126 assertions.
- [x] Focused Diagnostics tests passed: 8 tests, 68 assertions.
- [x] PHPUnit full suite passed: 216 tests, 1099 assertions, 2 expected skips.
- [x] PHPCS passed across 109 files.
- [x] Build passed: `npm.cmd run build`.
- [x] npm audit passed: 0 vulnerabilities at moderate threshold.
- [x] Composer audit passed via local `php .\composer.phar audit`: no security vulnerability advisories found.
- [x] `git diff --check` passed.
- [x] Translation-template coverage checked for this patch release. No new runtime strings were added; POT version metadata was manually aligned to `0.1.44`.
- [ ] `npm.cmd run pot` blocked: `wp` CLI is not on PATH in this environment. Run `wp i18n make-pot` on a machine with WP-CLI before or during release packaging if generated POT provenance is required.
- [ ] Release ZIP audit for `0.1.44`.
- [ ] GitHub release for `v0.1.44`.
- [ ] Updater install/update smoke verification not yet run for `0.1.44`.
- [ ] Live dashboard deployment/update on `control-sitesmanage` not yet performed.

## Open Items

- [ ] Commit `0.1.44` release-prep metadata/checklist changes after approval.
- [ ] Push local `0.1.44` release commit to `origin/master` and verify CI.
- [ ] Tag/publish `v0.1.44` and verify release asset packaging after CI passes.
- [ ] Run release ZIP audit for `0.1.44`.
- [ ] Deploy/update dashboard plugin on `control-sitesmanage` only after live-site approval.
