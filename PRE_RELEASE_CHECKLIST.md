# Alynt Drime Backups Dashboard Pre-Release Checklist

Updated: 2026-09-26

Use this checklist to track release-candidate readiness for the `Alynt Drime Backups Dashboard` plugin. Mark a workflow complete only after current evidence has passed for the recorded candidate.

## Current Release Candidate

- Candidate version: `0.1.45`
- Previous published release: `v0.1.44`
- Candidate purpose: release behavior-preserving dashboard structure cleanup that split Diagnostics local actions and Site Detail local record visibility/archive panels into focused traits.
- Boundary: release-prep source/docs/package metadata only. This candidate does not deploy/update `control-sitesmanage`, create backups, restore, delete, clean up, change WPvivid/server-runner schedules, execute rollback, change credentials, store Drime API credentials, run arbitrary commands, or perform database/server actions.
- Current checklist note: rows updated on 2026-09-26 reflect the current `0.1.45` release candidate after the targeted ds3 structure validation, release-prep metadata update, and local validation commands listed below. The full DS3 pre-release workflow was not rerun because this candidate is a small behavior-preserving structure cleanup.

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
- [x] Structure cleanup is implemented locally in commits `30d9dcf`, `6feaf96`, and POT refresh commit `79de600` after `v0.1.44`.

## Pre-Release Review Sequence

- [x] 01 Code Cleanup Review: targeted cleanup scan on 2026-09-26 found no debug/TODO/dangerous-function remnants in the structure-cleanup candidate.
- [x] 02 File Structure Review: targeted ds3 structure validation completed on 2026-09-26. Runtime files remain under the 300-line threshold; oversized files are tests only.
- [x] 03 Error Handling Review: no behavior-changing error paths were introduced; existing admin action notice/recovery paths were preserved.
- [x] 04 WP Best Practices Review: WordPress APIs, translatable strings, nonces/capability gates, and existing admin patterns retained.
- [x] 05 Database Review: no schema/table migration introduced for `0.1.45`; existing local record behavior is unchanged.
- [x] 06 Performance Review: no scheduled poll broadening and no Drime/API browsing added; rollback-preview lookup is bounded to existing local action history.
- [x] 07 Edge Cases Review: rollback-preview remains hidden unless the latest client capabilities and a fresh successful Schedule Apply record support it; stale/missing metadata is rejected.
- [x] 07A Adversarial Test-Suite Review: focused tests and full PHPUnit baseline cover capability gating, dispatcher payloads, repository rollback-preview lookups, Diagnostics counts, and rendering.
- [x] 08 Uninstall Review: no new persistent table/option/schedule ownership added in this slice.
- [x] 09 I18N Review: user-facing strings remain wrapped with the correct `alynt-drime-backups-dashboard` text domain. POT was regenerated with the local WP-CLI phar; WP-CLI emitted bundled dependency deprecation notices but exited successfully.
- [x] 10 Accessibility Review: rollback-preview controls use existing admin form patterns, visible explanatory copy, nonce/capability gates, and no keyboard-hostile custom controls.
- [x] 11 Code Quality Review: PHPUnit, PHPCS, build, npm audit, Composer audit, whitespace checks, release metadata review, and source-size inventory passed for current local candidate.
- [x] 12 Documentation Review: checklist, readme, README, changelog, protocol/threat-model/design docs, and implementation-plan wording describe rollback preview as non-mutating and separately gated.
- [x] 13 Security Audit: targeted scans found no new dangerous runtime PHP patterns; request/DB usage remains sanitized/prepared; rollback-preview requires signed action dispatch and client-side support, and dashboard does not receive Drime credentials.

## Release Validation

- [x] Main plugin PHP syntax check passed.
- [x] Focused Site Detail polling-state rendering tests passed during structure validation: 30 tests, 126 assertions.
- [x] PHPUnit full suite passed: 216 tests, 1099 assertions, 2 expected skips.
- [x] PHPCS passed across 111 files.
- [x] Build passed: `npm.cmd run build`.
- [x] npm audit passed: 0 vulnerabilities at moderate threshold.
- [x] Composer audit passed via local `php .\composer.phar audit`: no security vulnerability advisories found.
- [x] `git diff --check` passed.
- [x] Translation-template coverage checked for this patch release. POT was regenerated with the local WP-CLI phar and version metadata aligned to `0.1.45`.
- [ ] Release ZIP audit not yet run for `0.1.45`.
- [ ] GitHub release not yet created for `v0.1.45`.
- [ ] Updater install/update smoke verification not yet run for `0.1.45`.
- [ ] Live dashboard deployment/update on `control-sitesmanage` not yet performed.

## Open Items

- [ ] Commit `0.1.45` release-prep metadata/checklist changes.
- [ ] Push local `0.1.45` release commit to `origin/master` and verify CI.
- [ ] Tag/publish `v0.1.45` and verify release asset packaging after CI passes.
- [ ] Run release ZIP audit for `0.1.45`.
- [ ] Deploy/update dashboard plugin on `control-sitesmanage` only after live-site approval.
