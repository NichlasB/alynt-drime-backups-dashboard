# Alynt Drime Backups Dashboard Pre-Release Checklist

Updated: 2026-10-03

Use this checklist to track release-candidate readiness for the `Alynt Drime Backups Dashboard` plugin. Mark a workflow complete only after current evidence has passed for the recorded candidate.

## Current Release Candidate

- Candidate version: `0.1.55`
- Previous published release: `v0.1.54`
- Candidate purpose: release support-safe Diagnostics restore-readiness summary and source-level candidate counts.
- Boundary: release-prep source/docs/package metadata only. This candidate does not deploy/update `control-sitesmanage`, create backups, restore, delete, clean up, change WPvivid/server-runner schedules, execute rollback, change credentials, store Drime API credentials, run arbitrary commands, expose restore candidate references, or perform database/server actions.
- Current checklist note: rows updated on 2026-10-03 reflect the current `0.1.55` release candidate after the targeted feature validation, local release-prep metadata update, local PHPUnit/PHPCS/build/audit checks, and pending package verification. The full DS3 pre-release workflow was not rerun because this candidate is a small Diagnostics display/metadata patch built on already-pushed commits with passing GitHub CI.

## Prerequisites

- [x] Build tooling present: `package.json`, Node build script, PHPUnit, PHPCS/WPCS, Composer dev tooling, GitHub release workflow.
- [x] Observability present: Diagnostics tab, support-safe export/copy, redacted polling/action/source aggregates.
- [x] Updater compatibility present: GitHub Plugin URI and prior GitHub release/update flow established through previous dashboard releases.
- [x] Restore point/rollback baseline available through local Git history and current `v0.1.54` release/tag. No live-site, database, deployment, or destructive action was performed in this pass.

## Current Feature Workflow Tracking

- [x] V2.3 preview-only schedule visibility implemented locally.
- [x] DS2 Feature Light Review completed. Result: small hardening/polish issues found and fixed.
- [x] DS2 Feature Bloat And Structure Phase 1 completed. Result: changed large files are existing cohesive architecture/test files; no safe feature-stage split forced.
- [x] DS2 UI/UX Review completed. Result: native admin copy/buttons/status presentation retained; schedule UI is preview-only and non-mutating.
- [x] DS2 Security Review completed. Result: schedule capability sanitizer now ignores unsupported schedule IDs and never enables apply/rollback.
- [x] Current `0.1.55` patch scope is implemented in commits `c2d3303` and `ad02918` after `v0.1.54`, plus the release-prep metadata commit in this repository.

## Pre-Release Review Sequence

- [x] 01 Code Cleanup Review: targeted release-prep review on 2026-10-03 found the `0.1.55` patch limited to support-safe Diagnostics restore-readiness display and version/release metadata.
- [x] 02 File Structure Review: no new runtime files or architecture splits were introduced for `0.1.55`; existing oversized test/trait files remain tracked for a dedicated structure pass rather than risky release-prep splitting.
- [x] 03 Error Handling Review: no behavior-changing error paths were introduced; existing Diagnostics rendering fallbacks were preserved.
- [x] 04 WP Best Practices Review: WordPress APIs, translatable strings, escaping, nonces/capability gates, and existing admin patterns retained.
- [x] 05 Database Review: no schema/table migration introduced for `0.1.55`; existing local record behavior is unchanged.
- [x] 06 Performance Review: no scheduled poll broadening and no Drime/API browsing added; summary/count rows are derived from already-sanitized bounded Diagnostics aggregates.
- [x] 07 Edge Cases Review: restore-readiness summary states distinguish complete, partial, incomplete, stale, mixed, incompatible, unknown, and unavailable evidence without exposing restore controls.
- [x] 07A Adversarial Test-Suite Review: focused Diagnostics tests and full PHPUnit baseline cover summary/count rendering and support-safe output boundaries.
- [x] 08 Uninstall Review: no new persistent table/option/schedule ownership added in this slice.
- [x] 09 I18N Review: new Diagnostics labels are wrapped with the correct `alynt-drime-backups-dashboard` text domain and POT version metadata is aligned to `0.1.55`.
- [x] 10 Accessibility Review: Diagnostics rows use existing semantic table markup and do not add custom keyboard controls.
- [x] 11 Code Quality Review: local release-prep PHPUnit, PHPCS, build, npm audit, Composer audit, and whitespace checks passed for the current candidate; CI and package audit are pending after push/release.
- [x] 12 Documentation Review: checklist, readme, README, changelog, and implementation-plan wording describe restore-readiness evidence as support-safe and non-restorative.
- [x] 13 Security Audit: no new remote action, credential, Drime token, backup creation, restore, cleanup/delete, rollback execution, arbitrary command, database, or live-site behavior was introduced.

## Release Validation

- [x] Main plugin PHP syntax check passed for `0.1.55`: `php -l .\alynt-drime-backups-dashboard.php`.
- [x] Focused Diagnostics tests passed for `0.1.55`: `AdminPageDiagnosticsRenderingTest` passed 6 tests / 39 assertions; `DiagnosticsTest` passed 11 tests / 112 assertions.
- [x] PHPUnit full suite passed for `0.1.55`: 235 tests / 1266 assertions / 2 expected skips.
- [x] PHPCS passed for `0.1.55`: 117 files.
- [x] Build passed: `npm.cmd run build`.
- [x] npm audit passed: 0 vulnerabilities at moderate threshold.
- [x] Composer audit passed: no security vulnerability advisories found.
- [x] `git diff --check` passed.
- [x] Translation-template coverage checked for this patch release. New Diagnostics labels are present and POT version metadata is aligned to `0.1.55`; full POT regeneration was not rerun because local WP-CLI scanning currently fails in this environment.
- [ ] Release ZIP audit passed for `0.1.55`.
- [ ] GitHub release created for `v0.1.55` and release asset uploaded.
- [ ] Updater install/update smoke verification not yet run for `0.1.55`.
- [ ] Live dashboard deployment/update on `control-sitesmanage` not yet performed.

## Open Items

- [x] Commit `0.1.55` release-prep metadata changes.
- [ ] Push local `0.1.55` release commit to `origin/master` and verify CI.
- [ ] Tag/publish `v0.1.55` and verify release asset packaging after CI passes.
- [ ] Run release ZIP audit for `0.1.55`.
- [ ] Deploy/update dashboard plugin on `control-sitesmanage` only after live-site approval.
