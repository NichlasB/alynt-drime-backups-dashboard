# Alynt Drime Backups Dashboard Pre-Release Checklist

Updated: 2026-10-06

Use this checklist to track release-candidate readiness for the `Alynt Drime Backups Dashboard` plugin. Mark a workflow complete only after current evidence has passed for the recorded candidate.

## Current Release Candidate

- Candidate version: `0.1.62`
- Previous published release: `v0.1.61`
- Candidate purpose: release a low-risk display/readiness patch after the deployed `0.1.61` dashboard release: dashboard-local retained-record removal planning, Site Detail retained-record preview evidence, Diagnostics aggregate local removal-readiness evidence, and compact archived-row readiness hints.
- Boundary: release-prep source/docs/package metadata plus display-only local readiness evidence. This candidate does not deploy/update `control-sitesmanage`, permanently remove dashboard records, create backups, restore, delete, clean up, change WPvivid/server-runner schedules, execute rollback, change credentials, store Drime API credentials, run arbitrary commands, expose restore candidate references, or perform database/server actions.
- Current checklist note: rows updated on 2026-10-06 for the `0.1.62` retained-record readiness candidate. The full DS3 pre-release workflow was not rerun because this candidate is a bounded dashboard-local display/readiness patch with passing targeted tests, full PHPUnit/PHPCS/build checks, and no destructive, remote-action, protocol, schema, credential, backup, restore, cleanup/delete apply, Drime, live-site, or deployment behavior.

## Prerequisites

- [x] Build tooling present: `package.json`, Node build script, PHPUnit, PHPCS/WPCS, Composer dev tooling, GitHub release workflow.
- [x] Observability present: Diagnostics tab, support-safe export/copy, redacted polling/action/source aggregates.
- [x] Updater compatibility present: GitHub Plugin URI and prior GitHub release/update flow established through previous dashboard releases.
- [x] Restore point/rollback baseline available through local Git history and current `v0.1.61` release/tag. No live-site, database, deployment, or destructive action was performed in this pass.

## Current Feature Workflow Tracking

- [x] Current `0.1.62` patch scope is implemented in commits `deac3d1`, `a3573ea`, `7aa27c6`, and `1534231`, plus the release-prep metadata commit in this repository.
- [x] DS2/DS3 full workflow rerun deferred by scope: this is a bounded dashboard-local planning/display/readiness patch with no destructive local removal, remote action, protocol, schema, credential, Drime, backup, restore, cleanup/delete apply, schedule, live-site, release-deploy, or client-site behavior changes.

## Pre-Release Review Sequence

- [x] 01 Code Cleanup Review: targeted release-prep review on 2026-10-06 found the `0.1.62` patch limited to dashboard-local planning/display/readiness evidence.
- [x] 02 File Structure Review: the `0.1.62` patch adds small helper/display paths only and does not introduce large-file bloat or broad production refactors.
- [x] 03 Error Handling Review: no destructive workflow or behavior-changing error path was introduced; existing preview/readiness fallbacks were preserved.
- [x] 04 WP Best Practices Review: WordPress APIs, translatable strings, escaping, nonces/capability gates, and existing admin patterns retained.
- [x] 05 Database Review: no schema/table migration introduced for `0.1.61`; existing local record behavior is unchanged.
- [x] 06 Performance Review: no scheduled poll broadening and no Drime/API browsing added; summary/count rows are derived from already-sanitized bounded Diagnostics aggregates.
- [x] 07 Edge Cases Review: no edge-case logic changed; existing restore-readiness and Diagnostics behavior is preserved by focused tests.
- [x] 07A Adversarial Test-Suite Review: focused Diagnostics and local-record rendering tests plus the full PHPUnit baseline cover support-safe output boundaries and confirm no form/control is introduced by the archived-row hint.
- [x] 08 Uninstall Review: no new persistent table/option/schedule ownership and no uninstall behavior changes were introduced in this slice.
- [x] 09 I18N Review: new translatable strings are covered in the POT, and the POT release header was aligned to `0.1.62`.
- [x] 10 Accessibility Review: Diagnostics and archived-row hints use existing semantic table/description markup and do not add custom keyboard controls.
- [x] 11 Code Quality Review: local release-prep PHPUnit, PHPCS, build, whitespace checks, GitHub CI, release workflow, and package audit are required for the current candidate.
- [x] 12 Documentation Review: checklist, plugin readme, changelog, and implementation-plan wording describe `0.1.62` as display/readiness-only and preserve the existing dashboard boundaries.
- [x] 13 Security Audit: no permanent removal, new remote action, credential, Drime token, backup creation, restore, cleanup/delete apply, rollback execution, arbitrary command, database write, or live-site behavior was introduced.

## Release Validation

- [x] Main plugin PHP syntax check passed for `0.1.62`: `php -l .\alynt-drime-backups-dashboard.php`.
- [x] Focused retained-record readiness tests passed for `0.1.62`: `AdminPageSiteDetailLocalRecordRenderingTest` passed 9 tests / 35 assertions; `DiagnosticsTest` passed 3 tests / 34 assertions.
- [x] PHPUnit full suite passed for `0.1.62`: 243 tests / 1319 assertions / 2 expected skips.
- [x] PHPCS passed for `0.1.62`: 134 files.
- [x] Build passed for `0.1.62`: `npm.cmd run build`.
- [x] npm audit passed for `0.1.62`: 0 vulnerabilities at moderate threshold.
- [x] Composer audit passed for `0.1.62`: no security vulnerability advisories found.
- [x] `git diff --check` passed for `0.1.62`.
- [x] Translation-template coverage checked for this patch release. New strings were added manually because local `wp` is not available on PATH; POT header aligned to `0.1.62`.
- [x] Release ZIP audit passed for `0.1.62`: release asset `alynt-drime-backups-dashboard-0.1.62.zip` uploaded by Build Release run `37510850974`, SHA-256 digest `515c368d4d16a353956818a4d18e7e99ab4a05abb9508590d9d29a9939d569bf`, single top-level plugin folder, no excluded source/dev folders, and expected plugin/readme/POT/dist metadata present at `0.1.62`.
- [x] GitHub release created for `v0.1.62`: https://github.com/NichlasB/alynt-drime-backups-dashboard/releases/tag/v0.1.62.
- [ ] Updater install/update smoke verification not yet run for `0.1.62`.
- [ ] Live dashboard deployment/update on `control-sitesmanage` not yet performed.

## Open Items

- [x] Commit `0.1.62` release-prep metadata changes: `996c658`.
- [x] Push local `0.1.62` release commit to `origin/master` and verify CI: run `37510595089` passed.
- [x] Tag/publish `v0.1.62` and verify release asset packaging after CI passes: release workflow run `37510850974` passed.
- [x] Run release ZIP audit for `0.1.62`.
- [ ] Deploy/update dashboard plugin on `control-sitesmanage` only after live-site approval and fresh restore point.
