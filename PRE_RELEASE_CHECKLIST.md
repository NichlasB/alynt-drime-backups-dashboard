# Alynt Drime Backups Dashboard Pre-Release Checklist

Updated: 2026-09-26

Use this checklist to track release-candidate readiness for the `Alynt Drime Backups Dashboard` plugin. Mark a workflow complete only after current evidence has passed for the recorded candidate.

## Current Release Candidate

- Candidate version: `0.1.46`
- Previous published release: `v0.1.45`
- Candidate purpose: release compact rollback-preview readiness pills and reconcile completed implementation-roadmap status without adding remote powers or changing dashboard/client behavior.
- Boundary: release-prep source/docs/package metadata only. This candidate does not deploy/update `control-sitesmanage`, create backups, restore, delete, clean up, change WPvivid/server-runner schedules, execute rollback, change credentials, store Drime API credentials, run arbitrary commands, or perform database/server actions.
- Current checklist note: rows updated on 2026-09-26 reflect the current `0.1.46` release candidate after the targeted release validation, release-prep metadata update, GitHub CI, release workflow, and release ZIP audit listed below. The full DS3 pre-release workflow was not rerun because this candidate is a small UI/status and documentation reconciliation patch.

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
- [x] Current `0.1.46` patch scope is implemented in commits `88c3004`, `a382627`, `2fe524c`, and release-prep commit `eb8ba54` after `v0.1.45`.

## Pre-Release Review Sequence

- [x] 01 Code Cleanup Review: targeted release-prep review on 2026-09-26 found the `0.1.46` patch limited to rollback-preview readiness display, roadmap/docs reconciliation, and version/release metadata.
- [x] 02 File Structure Review: no new files or architecture splits were introduced for `0.1.46`; the release ZIP excludes development/test/docs/build sources.
- [x] 03 Error Handling Review: no behavior-changing error paths were introduced; existing admin action notice/recovery paths were preserved.
- [x] 04 WP Best Practices Review: WordPress APIs, translatable strings, escaping, nonces/capability gates, and existing admin patterns retained.
- [x] 05 Database Review: no schema/table migration introduced for `0.1.46`; existing local record behavior is unchanged.
- [x] 06 Performance Review: no scheduled poll broadening and no Drime/API browsing added; readiness labels are derived from existing bounded local action history.
- [x] 07 Edge Cases Review: rollback-preview readiness states distinguish hidden, waiting, blocked/expired, and ready while keeping controls capability-gated.
- [x] 07A Adversarial Test-Suite Review: focused AdminPageScheduleManagement tests and full PHPUnit baseline cover readiness rendering and prevent rollback execution controls from appearing incorrectly.
- [x] 08 Uninstall Review: no new persistent table/option/schedule ownership added in this slice.
- [x] 09 I18N Review: new readiness labels are wrapped with the correct `alynt-drime-backups-dashboard` text domain and POT version metadata is aligned to `0.1.46`.
- [x] 10 Accessibility Review: readiness pills are plain text status indicators in existing admin detail markup and do not add keyboard-hostile custom controls.
- [x] 11 Code Quality Review: PHPUnit, PHPCS, build, npm audit, Composer audit, whitespace checks, release metadata review, CI, and release ZIP audit passed for current candidate.
- [x] 12 Documentation Review: checklist, readme, README, changelog, and implementation-plan wording describe rollback preview as non-mutating and separately gated.
- [x] 13 Security Audit: no new remote action, credential, Drime token, backup creation, restore, cleanup/delete, rollback execution, arbitrary command, database, or live-site behavior was introduced.

## Release Validation

- [x] Main plugin PHP syntax check passed.
- [x] Focused AdminPageScheduleManagement tests passed: 8 tests, 47 assertions.
- [x] PHPUnit full suite passed: 218 tests, 1111 assertions, 2 expected skips.
- [x] PHPCS passed across 111 files.
- [x] Build passed: `npm.cmd run build`.
- [x] npm audit passed: 0 vulnerabilities at moderate threshold.
- [x] Composer audit passed via local `php .\composer.phar audit`: no security vulnerability advisories found.
- [x] `git diff --check` passed.
- [x] Translation-template coverage checked for this patch release. New readiness labels are present and POT version metadata is aligned to `0.1.46`; full POT regeneration was not rerun because local WP-CLI scanning currently fails when stale release package directories are present.
- [x] Release ZIP audit passed for `0.1.46`: GitHub release asset `alynt-drime-backups-dashboard-0.1.46.zip` downloaded to `C:\Users\Captain\Documents\AI Workflows\work\dashboard-release-0.1.46`, inspected with a single `alynt-drime-backups-dashboard/` top-level folder, 116 runtime files, expected `0.1.46` plugin/readme/POT metadata, no dev/test/docs/vendor/node/build workflow files, package main-file PHP syntax passed, and SHA256 `59aacc325f618d742a2a306505d83ec154760a1eee1b4e0f3b6a50244e6f73ad`.
- [x] GitHub release created: `https://github.com/NichlasB/alynt-drime-backups-dashboard/releases/tag/v0.1.46`. CI run `36251189533` passed on PHP 8.3 and PHP 7.4 for release-prep commit `eb8ba54`; Build Release workflow `36251239284` passed and uploaded the release asset.
- [ ] Updater install/update smoke verification not yet run for `0.1.46`.
- [ ] Live dashboard deployment/update on `control-sitesmanage` not yet performed.

## Open Items

- [x] Commit `0.1.46` release-prep metadata changes: `eb8ba54`.
- [x] Push local `0.1.46` release commit to `origin/master` and verify CI: run `36251189533` passed on PHP 8.3 and PHP 7.4.
- [x] Tag/publish `v0.1.46` and verify release asset packaging after CI passes.
- [x] Run release ZIP audit for `0.1.46`.
- [ ] Deploy/update dashboard plugin on `control-sitesmanage` only after live-site approval.
