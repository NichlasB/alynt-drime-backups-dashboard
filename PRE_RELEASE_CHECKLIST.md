# Alynt Drime Backups Dashboard Pre-Release Checklist

Updated: 2026-09-12

Use this checklist to track release-candidate readiness for the `Alynt Drime Backups Dashboard` plugin. Mark a workflow complete only after current evidence has passed for the recorded candidate.

## Current Release Candidate

- Candidate version: `0.1.20`
- Previous published release: `v0.1.19`
- Candidate purpose: V2.3 preview-only schedule visibility for the Alynt scan/upload schedule capability, plus release metadata and translation-template alignment.
- Boundary: read-only schedule capability display only. No schedule apply, disable, rollback, backup creation, restore, cleanup, settings, credential, Drime-token, or arbitrary-command action is included.

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
- [x] Release-prep metadata bumped to `0.1.20`.

## Pre-Release Review Sequence

- [x] 01 Code Cleanup Review: no TODO/FIXME/debug remnants found by targeted source scans.
- [x] 02 File Structure Review: no new feature-stage split required; oversized files are existing aggregate/trait/test surfaces already covered by structure review deferral.
- [x] 03 Error Handling Review: status, schedule-capability, and unavailable-capability paths render explicit non-mutating feedback.
- [x] 04 WP Best Practices Review: WordPress APIs, translatable strings, nonces/capability gates, and existing admin patterns retained.
- [x] 05 Database Review: no schema/table migration introduced for V2.3; existing snapshot storage handles additive sanitized payload fields.
- [x] 06 Performance Review: no new remote calls, loops over bounded schedule arrays only, Diagnostics counts aggregate sanitized snapshot data.
- [x] 07 Edge Cases Review: missing capability, unsupported schedule IDs, early mutation claims, and absent schedules are covered by tests.
- [x] 07A Adversarial Test-Suite Review: regression coverage added for unsupported schedule filtering and preview-only rendering/no form controls.
- [x] 08 Uninstall Review: no new persistent table/option/schedule ownership added in this slice.
- [x] 09 I18N Review: new schedule strings were added to the POT manually because local `wp i18n make-pot` is unavailable.
- [x] 10 Accessibility Review: UI is static/read-only, uses existing WordPress-native buttons/status text, and does not add dynamic focus/modal behavior.
- [x] 11 Code Quality Review: PHPUnit, PHPCS, build, audit, and whitespace checks passed for candidate `0.1.20`.
- [x] 12 Documentation Review: changelog, README, readme, implementation/protocol/threat-model planning docs describe preview-only boundaries.
- [x] 13 Security Audit: focused scans found no dangerous runtime PHP patterns; request/DB usage remains sanitized/prepared; dashboard does not receive Drime credentials.

## Release Validation

- [x] PHP syntax sweep passed for 98 non-vendor/build PHP files.
- [x] Focused uninstall safety tests passed: 3 tests, 14 assertions.
- [x] PHPUnit passed: 153 tests, 719 assertions, 2 expected skips.
- [x] PHPCS passed across 65 files.
- [x] Build passed: `npm.cmd run build`.
- [x] npm audit passed: 0 vulnerabilities at high threshold.
- [x] Composer audit passed: no security vulnerability advisories found.
- [x] `git diff --check` passed.
- [x] Translation-template coverage checked for new V2.3 strings; missing strings were patched manually.
- [ ] `npm.cmd run pot` blocked: `wp` CLI is not on PATH in this environment. Run `wp i18n make-pot` on a machine with WP-CLI before or during release packaging if generated POT provenance is required.
- [ ] Release ZIP audit not yet run for `0.1.20`.
- [ ] GitHub release not yet created.
- [ ] Updater install/update smoke verification not yet run for `0.1.20`.
- [ ] Live dashboard deployment/update on `control-sitesmanage` not yet performed.

## Open Items

- [ ] Commit current DS3 checklist/i18n reconciliation changes.
- [ ] Run release-package creation and ZIP audit after approval.
- [ ] Push/tag/publish `v0.1.20` only after release approval.
- [ ] Deploy/update dashboard plugin on `control-sitesmanage` only after live-site approval.
