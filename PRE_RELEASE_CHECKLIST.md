# Alynt Drime Backups Dashboard Pre-Release Checklist

Updated: 2026-10-06

Use this checklist to track release-candidate readiness for the `Alynt Drime Backups Dashboard` plugin. Mark a workflow complete only after current evidence has passed for the recorded candidate.

## Current Release Candidate

- Candidate version: `0.1.61`
- Previous published release: `v0.1.60`
- Candidate purpose: release a low-risk maintenance patch after the deployed `0.1.60` dashboard release: rollout-state documentation, remote-action summary docblock cleanup, focused enrollment REST and remote-action repository test-support splits, Unreleased changelog coverage, and a documented patch-release planning gate.
- Boundary: release-prep source/docs/package metadata only. This candidate does not deploy/update `control-sitesmanage`, create backups, restore, delete, clean up, change WPvivid/server-runner schedules, execute rollback, change credentials, store Drime API credentials, run arbitrary commands, expose restore candidate references, or perform database/server actions.
- Current checklist note: rows updated on 2026-10-06 reflect the current `0.1.61` maintenance release candidate after local release-prep metadata updates, local PHPUnit/PHPCS/build checks, npm audit, Composer audit, and whitespace validation. The full DS3 pre-release workflow was not rerun because this candidate is a small documentation/test-support/formatting patch built on already-pushed commits with passing GitHub CI and no runtime behavior changes.

## Prerequisites

- [x] Build tooling present: `package.json`, Node build script, PHPUnit, PHPCS/WPCS, Composer dev tooling, GitHub release workflow.
- [x] Observability present: Diagnostics tab, support-safe export/copy, redacted polling/action/source aggregates.
- [x] Updater compatibility present: GitHub Plugin URI and prior GitHub release/update flow established through previous dashboard releases.
- [x] Restore point/rollback baseline available through local Git history and current `v0.1.60` release/tag. No live-site, database, deployment, or destructive action was performed in this pass.

## Current Feature Workflow Tracking

- [x] Current `0.1.61` patch scope is implemented in commits `01ba9b0`, `11346f7`, `6eccf02`, `07b4063`, `de13286`, `f5e0f9c`, plus the release-prep metadata commit in this repository.
- [x] DS2/DS3 full workflow rerun deferred by scope: this is a docs/test-support/formatting patch with no production behavior, UI, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release-deploy, or remote-action behavior changes.

## Pre-Release Review Sequence

- [x] 01 Code Cleanup Review: targeted release-prep review on 2026-10-06 found the `0.1.61` patch limited to documentation, formatting, and test-support splits.
- [x] 02 File Structure Review: the `0.1.61` patch splits test-support doubles only and does not change production helper structure.
- [x] 03 Error Handling Review: no behavior-changing error paths were introduced; existing Diagnostics rendering fallbacks were preserved.
- [x] 04 WP Best Practices Review: WordPress APIs, translatable strings, escaping, nonces/capability gates, and existing admin patterns retained.
- [x] 05 Database Review: no schema/table migration introduced for `0.1.61`; existing local record behavior is unchanged.
- [x] 06 Performance Review: no scheduled poll broadening and no Drime/API browsing added; summary/count rows are derived from already-sanitized bounded Diagnostics aggregates.
- [x] 07 Edge Cases Review: no edge-case logic changed; existing restore-readiness and Diagnostics behavior is preserved by focused tests.
- [x] 07A Adversarial Test-Suite Review: focused Diagnostics tests and full PHPUnit baseline cover summary/count rendering and support-safe output boundaries.
- [x] 08 Uninstall Review: no new persistent table/option/schedule ownership added in this slice.
- [x] 09 I18N Review: no new translatable strings were introduced. The POT release header was aligned to `0.1.61`; no runtime string extraction changes were needed.
- [x] 10 Accessibility Review: Diagnostics rows use existing semantic table markup and do not add custom keyboard controls.
- [x] 11 Code Quality Review: local release-prep PHPUnit, PHPCS, build, npm audit, Composer audit, and whitespace checks passed for the current candidate; CI and package audit are pending after push/release.
- [x] 12 Documentation Review: checklist, readme, README, changelog, and implementation-plan wording describe `0.1.61` as maintenance-only and preserve the existing dashboard boundaries.
- [x] 13 Security Audit: no new remote action, credential, Drime token, backup creation, restore, cleanup/delete, rollback execution, arbitrary command, database, or live-site behavior was introduced.

## Release Validation

- [x] Main plugin PHP syntax check passed for `0.1.61`: `php -l .\alynt-drime-backups-dashboard.php`.
- [x] Focused structure tests passed during the local maintenance line: `EnrollmentRestControllerRejectionTest` passed 6 tests / 39 assertions; `RemoteActionRepository` passed 11 tests / 89 assertions.
- [x] PHPUnit full suite passed for `0.1.61`: 238 tests / 1285 assertions / 2 expected skips.
- [x] PHPCS passed for `0.1.61`: 134 files.
- [x] Build passed for `0.1.61`: `npm.cmd run build`.
- [x] npm audit passed: 0 vulnerabilities at moderate threshold.
- [x] Composer audit passed: no security vulnerability advisories found.
- [x] `git diff --check` passed.
- [x] Translation-template coverage checked for this patch release. No new strings were introduced; POT header aligned to `0.1.61`.
- [ ] Release ZIP audit not yet run for `0.1.61`; pending release asset creation.
- [ ] GitHub release not yet created for `v0.1.61`.
- [ ] Updater install/update smoke verification not yet run for `0.1.61`.
- [ ] Live dashboard deployment/update on `control-sitesmanage` not yet performed.

## Open Items

- [ ] Commit `0.1.61` release-prep metadata changes.
- [ ] Push local `0.1.61` release commit to `origin/master` and verify CI.
- [ ] Tag/publish `v0.1.61` and verify release asset packaging after CI passes.
- [ ] Run release ZIP audit for `0.1.61`.
- [ ] Deploy/update dashboard plugin on `control-sitesmanage` only after live-site approval.
