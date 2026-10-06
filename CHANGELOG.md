# Changelog

All notable changes to this project will be documented in this file.

## Unreleased

### Changed

- Reconciled the implementation plan with the deployed `0.1.62` dashboard rollout state and post-poll health evidence.
- Refreshed hook reference wording so it names the current `0.1.62` release line.
- Recorded that permanent local retained-record removal remains feasible but deferred by default until there is a concrete operational, data-minimization, performance, or compliance need.

## 0.1.62 - 2026-10-06

### Added

- Added a planning-only design for a future dashboard-local retained-record permanent removal flow, explicitly keeping implementation gated because it would be a destructive local database action.
- Added a Site Detail Local Removal Preview for archived retained records so operators can see future-removal eligibility and retained snapshot/action row counts without any delete or confirmation action.
- Added support-safe Diagnostics aggregate local removal-readiness counts for archived records so operators can see ready, blocked, retained snapshot, retained action, and non-terminal action totals without opening each record.
- Added compact archived-row local removal-readiness hints in the Archived Local Records view.

### Changed

- Aligned Settings and Hook reference documentation with the current `0.1.61` release line, stored option set, custom table ownership, and dashboard REST route boundary.

### Security

- Preserved the current runtime boundary: the retained-record preview is display-only and adds no database write, delete control, remote action, backup creation, restore, cleanup/delete apply, credential handling, Drime behavior, release, deployment, or live-site state change.
- Preserved the dashboard-local removal boundary: `0.1.62` adds only planning and display/readiness evidence. It does not add a permanent remove/delete control, confirmation form, POST handler, database write, remote action, client-site mutation, backup creation, restore, cleanup/delete apply, credential handling, Drime behavior, or live-site state change.

## 0.1.61 - 2026-10-06

### Changed

- Reconciled the implementation plan with the deployed `0.1.60` dashboard rollout state and post-poll health evidence.
- Continued test-support structure cleanup by splitting the enrollment REST repository double and remote-action repository `wpdb` double into focused support files without changing runtime behavior.
- Recorded the remote-action summary docblock cleanup and the post-`0.1.60` support-file split boundaries in the implementation plan.

### Security

- Preserved the existing dashboard boundaries: no production PHP behavior, protocol behavior, database schema, remote action, backup creation, restore, cleanup/delete, schedule change, credential handling, Drime behavior, live-site behavior, release behavior, or deployment behavior was introduced.

## 0.1.60 - 2026-10-06

### Changed

- Continued the file-structure cleanup pass with focused production helper splits and broad PHPUnit/support harness splits without changing dashboard runtime behavior.
- Reconciled release-history metadata and implementation-plan status for completed rollback-preview proof and current source-size baseline.
- Recorded that current non-build source and test-support files are below the earlier large-file risk threshold so future structure work can avoid speculative security-sensitive refactors.

### Security

- Preserved the existing dashboard boundaries: no new remote actions, backup creation, restore, cleanup/delete, schedule rollback, credential handling, Drime behavior, database schema, live-site behavior, or deployment behavior was introduced.

## 0.1.59 - 2026-10-05

### Changed

- Reconciled the implementation plan after the `0.1.58` release so Diagnostics Support Summary export is marked released, deployed, and post-release monitored.
- Split remote-action capability support checks into a focused helper trait without changing capability parsing, support decisions, dispatch behavior, protocol behavior, database schema, UI output, or live-site state.
- Split remote-action schedule result sanitizers into a focused helper trait without changing sanitized output, remote-action permissions, protocol behavior, database schema, UI output, or live-site state.

## 0.1.58 - 2026-10-05

### Added

- Added a Diagnostics Support Copy download control so operators can save the already-redacted support summary as JSON without selecting the textarea manually.

### Security

- Preserved the Diagnostics redaction boundary: the download uses only the already-rendered support-safe summary and does not add a server endpoint, protocol change, database write, remote action, raw payload exposure, credential handling, Drime behavior, or live-site mutation.

## 0.1.57 - 2026-10-04

### Added

- Added display-only latest cleanup-preview evidence on Site Detail when clients report sanitized V2.4 cleanup-preview results.

### Security

- Preserved the read-only cleanup boundary: no cleanup apply, deletion, retention change, restore, credential, Drime, filesystem-path, protocol, or schema behavior is added.

## 0.1.56 - 2026-10-03

### Changed

- Reconciled restore-readiness roadmap docs after the `0.1.55` release so Site Detail, Sites-row, and Diagnostics visibility work is marked released/deployed/monitored and further restore-adjacent work is explicitly separated from restore execution.
- Split cleanup-preview Remote Action History detail formatting into a focused helper trait to keep the main history helper under the preferred production-file size threshold without changing behavior.
- Split retained attention/recovery Diagnostics aggregate helpers into a focused metrics trait to keep the main site-metrics helper comfortably below the preferred production-file size threshold without changing behavior.

## 0.1.55 - 2026-10-03

### Added

- Added a support-safe Diagnostics restore-readiness summary row so operators can quickly understand whether reporting sites have complete, incomplete, stale, mixed, incompatible, unknown, or unavailable restore evidence.
- Added source-level restore-readiness Diagnostics counts for Server runner and WPvivid candidates, including reported, complete, and incomplete totals.

### Security

- Preserved the restore boundary: Diagnostics summaries and source counts do not expose candidate references, paths, filenames, package names, Drime identifiers, credentials, restore controls, or restore guarantees.

## 0.1.54 - 2026-10-03

### Added

- Added compact Sites-row restore evidence hints when clients report optional restore-readiness summaries.
- Kept detailed restore-readiness evidence on Site Detail while adding at-a-glance Sites-tab visibility.

### Security

- Preserved the restore boundary: Sites-row hints do not expose candidate references, paths, filenames, package names, Drime identifiers, credentials, restore controls, or restore guarantees.

## 0.1.53 - 2026-10-03

### Added

- Added support-safe Diagnostics and support-copy aggregate counts for optional restore-readiness evidence, including reporting sites, evidence states, and complete/incomplete source candidate totals.

### Changed

- Removed optional watch-only npm development helpers that depended on a vulnerable `braces` transitive package while preserving the supported build, lint, test, POT, and deploy scripts.

### Security

- Preserved the restore boundary: the Diagnostics aggregates do not expose candidate references, paths, filenames, package names, Drime identifiers, credentials, restore controls, or restore guarantees.

## 0.1.52 - 2026-10-02

### Added

- Added optional display-only restore-readiness evidence on Site Detail when clients report sanitized source-level restore candidate metadata.
- Added support-safe restore-readiness protocol, threat-model, implementation-plan, validator, rendering, and regression-test coverage.

### Security

- Preserved the restore boundary: this release does not restore, stage files, download backup packages, import databases, browse paths, expose raw candidate references, store dashboard Drime credentials, or mutate Drime/client state.

## 0.1.51 - 2026-10-01

### Added

- Added V2.4 Cleanup Preview dashboard controls for separately opted-in clients that advertise non-mutating cleanup-preview support for Alynt uploader-owned temporary artifacts.
- Added support-safe Cleanup Preview action-history, diagnostics, support-copy, protocol, threat-model, and implementation-plan documentation.
- Added cleanup-preview capability sanitization and signed preview-only intent dispatch for the `safe_local_uploader_owned` / `uploader_temp_artifacts` allowlist.

### Security

- Preserved the cleanup boundary: this release does not add cleanup apply, backup deletion, Drime cleanup/deletion, restore behavior, arbitrary filesystem browsing, credential mutation, dashboard Drime credential storage, deployment behavior, or live-site state changes.

## 0.1.50 - 2026-09-30

### Added

- Added a support-safe Dashboard Runtime panel to Diagnostics so operators can confirm the installed dashboard version and protocol/schema contract from the UI.

### Security

- Preserved the dashboard-local diagnostics boundary: this release does not change polling, classification, protocol behavior, database schema, remote-action permissions, backup creation, restore, delete, cleanup, credential handling, Drime credential storage, deployment behavior, or live-site state.

## 0.1.49 - 2026-09-30

### Added

- Added display-only latest rollback-preview proof evidence to Site Detail Schedule Management when dashboard-local action history contains a successful rollback-preview result.

### Security

- Preserved the rollback-preview boundary: this release does not execute rollback, mutate schedules during rollback preview, create backups, restore, delete, clean up, change credentials, store Drime API credentials, or run arbitrary commands.

## 0.1.48 - 2026-09-29

### Added

- Added support-safe Diagnostics aggregate counts and readable summaries for retained attention/recovery snapshot history, so operators can see fleet-level recent recovery and repeated-attention patterns without exposing client identifiers.

### Security

- Preserved the dashboard-local, read-only boundary: this release does not add schema changes, client protocol changes, backup creation, restore, delete, cleanup, schedule mutation, credential changes, Drime credential storage, or arbitrary command actions.

## 0.1.47 - 2026-09-29

### Added

- Added a Site Detail Attention / Recovery History panel derived from retained redacted snapshot summary fields so operators can understand transient attention states that later recover.

### Security

- Preserved the dashboard-local, read-only boundary: this release does not add schema changes, client protocol changes, backup creation, restore, delete, cleanup, schedule mutation, credential changes, Drime credential storage, or arbitrary command actions.

## 0.1.46 - 2026-09-26

### Changed

- Added compact rollback-preview readiness pills on Site Detail so hidden, waiting, blocked, expired, and ready states are easier to scan without implying rollback execution is available.
- Reconciled the implementation roadmap so completed backup-evidence, diagnostics, archive, source-policy, schedule-hint, and action-history slices are clearly marked as implemented/released.

### Security

- Preserved the V2.3 rollback boundary: this release does not execute rollback, mutate schedules during rollback preview, create backups, restore, delete, clean up, change credentials, store Drime API credentials, or run arbitrary commands.

## 0.1.45 - 2026-09-26

### Changed

- Split dashboard-local Diagnostics POST handlers into a focused admin action trait during structure cleanup.
- Split Site Detail local record visibility and archive panels into a focused rendering trait during structure cleanup.

## 0.1.44 - 2026-09-22

### Added

- Added explicit Site Detail Schedule Management rollback-preview readiness states so operators can distinguish hidden, waiting-for-apply-metadata, and ready non-mutating preview conditions.
- Added support-safe Diagnostics aggregate counts for rollback-preview-supported sites, rollback-preview-hidden sites, and rollback-apply-advertised canary evidence.

### Security

- Preserved the rollback-preview boundary: this release does not execute rollback, mutate schedules during rollback preview, create backups, restore, delete, clean up, change credentials, store Drime API credentials, or run arbitrary commands.

## 0.1.43 - 2026-09-21

### Added

- Added non-mutating Schedule Rollback Preview dispatch and Site Detail controls for clients that separately advertise rollback-preview support and have fresh support-safe rollback metadata from a successful Schedule Apply action.
- Added support-safe Schedule Rollback Preview action-history details and Diagnostics/action-summary counts.

### Changed

- Clarified rollback-preview labels so operators can distinguish a preview from any future rollback execution.

### Security

- Preserved the V2.3 boundary: this release does not execute rollback, mutate schedules during rollback preview, change WPvivid/server-runner schedules, create backups, restore, delete, clean up, change credentials, store Drime API credentials, or run arbitrary commands.

## 0.1.42 - 2026-09-21

### Changed

- Added compact Sites-row source reason lines so operators can see why source evidence is fresh, policy-valid, optional, stale, missing, or queued without opening Site Detail.

## 0.1.41 - 2026-09-20

### Added

- Added Site Detail Remote Action History filters for action type and dashboard state with active-filter summaries, reset links, and empty filtered-result messaging.

### Changed

- Compacted long Site Detail Remote Action History detail cells so cadence transitions stay visible by default while longer support-safe details remain available behind a native disclosure.
- Split backup-source evidence helper methods into focused compact-row and operator-detail traits during pre-release structure cleanup.

## 0.1.40 - 2026-09-20

### Changed

- Clarified V2.3 Schedule Apply wording so operators see that apply changes only the Alynt uploader scan cadence, not upload-worker, WPvivid, server-runner, Drime, restore, cleanup, or credential behavior.

### Fixed

- Avoided misleading Schedule Preview and Schedule Apply history rows such as `Unknown → every 15 minutes` while waiting for the client to report current or previous cadence evidence.

## 0.1.39 - 2026-09-19

### Changed

- Stacked the Sites tab archive-toggle helper copy below its button by default so the helper stays readable in constrained admin layouts.

## 0.1.38 - 2026-09-19

### Changed

- Polished the Sites tab archive-toggle helper layout so the button and explanatory copy stack cleanly on narrow admin screens.

## 0.1.37 - 2026-09-19

### Added

- Added dashboard-local archive/unarchive controls for revoked and expired pending dashboard records.
- Added an archived-records view so operators can keep audit history without crowding the default Sites and Attention workflows.

### Changed

- Excluded archived local records from default Sites, Attention, and scheduled polling contexts.
- Added archived-record counts to Diagnostics support summaries.
- Split archive admin action handlers into a focused trait during release prep.

## 0.1.36 - 2026-09-19

### Changed

- Added Site Detail guidance for locally revoked dashboard records so operators understand audit retention, re-enrollment requirements, and the absence of permanent local removal in this release.
- Clarified Sites-row schedule capability hints so preview-only clients and apply-gated clients are distinguishable without adding row-level schedule controls.

## 0.1.35 - 2026-09-19

### Added

- Added Diagnostics generated-at freshness evidence and a cache-busted refresh link.
- Sent no-cache headers for the dashboard admin page to reduce stale admin renders.

## 0.1.34 - 2026-09-18

### Changed

- Increased the scheduled dashboard polling batch size so the current enrolled fleet can be refreshed in one normal scheduled run while keeping bounded repository caps and explicit runtime limits intact.

## 0.1.33 - 2026-09-18

### Changed

- Clarified Diagnostics polling summaries with aggregate dashboard record-state counts so operators can distinguish total records from active polling records, pending pairings, awaiting-first-poll records, and locally revoked records.

## 0.1.32 - 2026-09-18

### Changed

- Clarified V2.3 Schedule Apply wording so operators see that changes are limited to future Alynt scan/upload cadence and rollback remains unavailable in this release.

## 0.1.31 - 2026-09-18

### Fixed

- Rounded compact Sites-tab backup source upload ages to readable minute/hour/day labels instead of raw seconds.

## 0.1.30 - 2026-09-18

### Changed

- Made Sites-tab backup evidence more compact with an at-a-glance backup-health summary and short per-source rows while keeping detailed reasons on Site Detail.

## 0.1.29 - 2026-09-18

### Added

- Added targeted remote-action signer regression coverage for unsupported PHP Sodium runtimes and malformed signature material.

### Changed

- Reused already-loaded Site Detail remote-action history when rendering V2 action panels, reducing duplicate repository reads.
- Added accessible names to display-once pairing-token and V2 action opt-in token copy fields.

### Fixed

- Treated clearing an already-empty diagnostics event buffer as a successful no-op.
- Wired scheduled maintenance to run completed remote-action retention cleanup alongside snapshot retention cleanup.

## 0.1.28 - 2026-09-17

### Changed

- Polished Diagnostics operator action history so dashboard-local and remote-action audit rows show operator-friendly labels, including Pause Polling and Resume Polling, instead of raw action slugs.
- Updated Operator Action History helper text so scheduled polling pause/resume controls are documented alongside pairing-token creation, Check Now, local revocation, and diagnostics changes.

## 0.1.27 - 2026-09-17

### Added

- Added dashboard-local scheduled polling Pause/Resume controls on Sites rows and Site Detail so operators can temporarily stop automatic polling for an enrolled record while keeping manual Check Now available.

### Changed

- Preserved the local-only boundary: pausing or resuming scheduled polling changes only dashboard-owned schedule state and does not contact client sites, start or stop backups, change settings, delete data, mutate Drime, or alter V2 remote-action capabilities.

## 0.1.26 - 2026-09-17

### Added

- Added clearer operator-facing backup source attention summaries on the Sites list and Site Detail screens so stale or missing evidence explains which source needs review and why.
- Added support-safe rollback readiness evidence for redacted Schedule Apply metadata, including Diagnostics aggregates that show whether preview/apply context is available without exposing raw payloads or secrets.

### Changed

- Preserved the current remote-action boundary: rollback remains unavailable, and the dashboard still cannot create backups, restore, delete, clean up, change WPvivid or server-runner schedules, store Drime credentials, or run arbitrary commands.

## 0.1.25 - 2026-09-15

### Fixed

- Polished Schedule Apply history details so applied schedule rows show the sanitized cadence transition and next run time reported by the client.
- Preserved support-safe Schedule Apply context fields in dashboard action history, including preview identifiers and cadence evidence, without storing raw payloads or secrets.

## 0.1.24 - 2026-09-15

### Added

- Added V2.3 guarded Schedule Apply support for the Alynt scan/upload schedule. The dashboard can dispatch a signed `schedule_apply` intent only from a fresh successful matching `schedule_preview` result when the client reports local Schedule Apply support.

### Changed

- Extended V2 action opt-in tokens to include `schedule_apply` for new opt-ins while keeping client-side Schedule Apply disabled unless a local client administrator separately enables that policy.
- Preserved the schedule-management boundary: no rollback runtime behavior, WPvivid schedule mutation, server-runner schedule mutation, backup creation, restore, cleanup, delete, Drime credential storage, arbitrary cron, or arbitrary command actions were added.

## 0.1.23 - 2026-09-14

### Fixed

- Preserved client-reported remote-action result codes and summaries when clients report the current `code` and `summary` fields, so schedule-preview action history can show the reconciled client result text.

## 0.1.22 - 2026-09-13

### Added

- Added V2.3 preview-only schedule change dispatch for separately opted-in clients. The dashboard can send a signed `schedule_preview` intent for the Alynt scan/upload schedule and show the redacted client result without changing schedules.

### Changed

- Kept schedule management read-only: this release does not apply, disable, roll back, or otherwise change client schedules, and it does not add backup creation, restore, cleanup, settings, credential, Drime-token, or arbitrary-command actions.
- Regenerated the dashboard POT with release-artifact directories excluded so translation source references come only from current runtime source.

## 0.1.21 - 2026-09-12

### Fixed

- Kept historical resolved failed upload counts visible while preventing them from forcing `needs_attention` when no matching queue remains and the current source evidence is otherwise healthy.

## 0.1.20 - 2026-09-12

### Added

- Added V2.3 preview-only schedule visibility for clients that report the Alynt scan/upload schedule capability, including Site Detail schedule posture, compact Sites-row hints, and support-safe Diagnostics aggregates.

### Changed

- Kept V2.3 schedule management strictly read-only: the dashboard sanitizes schedule capability evidence, ignores unsupported schedules, and does not apply, disable, roll back, or otherwise change schedules.
- Improved schedule interval display so minute-based cadences such as the 15-minute scan/upload interval are shown in human-readable form.

## 0.1.19 - 2026-09-10

### Added

- Added a dashboard-owned per-site WPvivid monitoring policy so a site can explicitly treat WPvivid as external/optional when WPvivid backups are intentionally handled outside Alynt-uploaded evidence.
- Added a Site Detail toggle for the WPvivid source policy, with Sites-list and Site-detail evidence labels showing `External / optional` when enabled.

### Changed

- Kept WPvivid external/optional policy read-only and dashboard-local: it suppresses stale/missing WPvivid upload-evidence attention only for that source while preserving attention for failed uploads, global failures, cron problems, incompatible payloads, polling failures, and unrelated warnings.

## 0.1.18 - 2026-09-09

### Fixed

- Allowed the dashboard host to dispatch the bounded V2.1 Request Backup Now action to its own same-origin uploader endpoint when managed-host DNS resolves the public hostname to a loopback/private address, while preserving public-IP enforcement for all non-same-origin client action destinations.

## 0.1.17 - 2026-09-08

### Fixed

- Fixed V2.2 remote-action reconciliation for newly dispatched Request Backup Now actions by storing the signed action UUID as the dashboard action record public ID, matching the client-reported `remote_actions.last_action.action_id`.

## 0.1.16 - 2026-09-08

### Added

- Added V2.2 dashboard-side reconciliation for V2 remote-action history. Successful status polls now reconcile sanitized client `remote_actions.last_action` evidence back to matching dashboard action records for the same site.
- Added support-safe client execution evidence fields for action history, including client state, result code/summary, bounded numeric counts, client timestamp, and reconciliation timestamp.
- Added stale-action detection for accepted/running actions that do not receive matching client confirmation within the expected window.
- Added compact Sites-row latest-client-action hints and clearer Site Detail history columns that distinguish dashboard request state from client-reported execution state.
- Added support-safe Diagnostics aggregate counts for remote-action history.

### Changed

- Preserved the V1 read-only status-polling boundary and the V2.1 `scan_upload_now` action boundary; this release does not add backup creation, restore, cleanup, schedule management, delete actions, Drime credential storage, filesystem browsing, or arbitrary commands.

## 0.1.15 - 2026-08-20

### Added

- Added V2.1 **Request Backup Now** for separately opted-in clients. The dashboard generates display-once `adb2a` action opt-in tokens, stores the matching signing key encrypted, dispatches signed `scan_upload_now` intents, records bounded redacted remote-action history, and may run a follow-up read-only status poll after client acceptance.
- Added an action-history cleanup index and uninstall purge coverage for dashboard-owned remote-action records.

### Changed

- Bumped the release candidate to `0.1.15` because `v0.1.14` is already used by the prior dashboard patch release.
- Improved V2.1 action-button accessibility by linking buttons to their explanatory guardrail text.

### Fixed

- Hardened dashboard activation so failed cron scheduling is reported instead of silently leaving polling or cleanup unscheduled.
- Hardened uninstall safety: rollback copies discovered by WordPress now exit before touching dashboard state, and canonical plugin deletion preserves dashboard records by default. A permanent dashboard-data purge now requires an explicit `wp-config.php` constant.

## 0.1.14 - 2026-08-20

### Fixed

- Hardened uninstall safety so WordPress-discovered rollback copies exit before touching canonical dashboard state.
- Preserved dashboard records by default during normal canonical plugin deletion.
- Required the explicit `ALYNT_DRIME_BACKUPS_DASHBOARD_PURGE_DATA_ON_UNINSTALL` `wp-config.php` constant for permanent dashboard-data removal.
- Documented safe rollback archive placement outside `wp-content/plugins`.

### Security

- Preserved the read-only dashboard boundary and added no remote actions.

## 0.1.13 - 2026-08-19

### Added

- Added an always-on, bounded, redacted operator action history for dashboard-local actions such as pairing-token creation, local revocation, manual **Check Now**, and diagnostics changes.

## 0.1.12 - 2026-08-18

### Fixed

- Allowed dashboard ingestion of WPvivid Pro/addon schedule policy basis labels reported by live uploader sites.

## 0.1.11 - 2026-08-18

### Added

- Added dashboard ingestion, classification, and display support for uploader-reported WPvivid schedule policy summaries, using detected client cadence when available and the 15-day dashboard fallback otherwise.

## 0.1.10 - 2026-08-17

### Added

- Added a dashboard-side WPvivid freshness policy so weekly or biweekly WPvivid backup evidence can remain healthy while server-runner evidence stays strictly monitored.

## 0.1.9 - 2026-08-17

### Fixed

- Aligned the Sites-tab helper copy with the shortened **Check Now** manual status-check button label.

## 0.1.8 - 2026-08-17

### Changed

- Added accessible busy-state feedback for pairing-token, diagnostics-event, and support-summary copy buttons.
- Added explicit WordPress date/time format fallbacks for admin timestamp rendering.
- Added v2 remote-action planning documentation while preserving the v1 read-only boundary.

### Fixed

- Treated malformed or empty stored status snapshot payloads as not reporting instead of falling through to a misleading configuration state.

### Tests

- Added focused status-classifier regression coverage for malformed JSON and empty decoded snapshot payloads.

## 0.1.7 - 2026-08-16

### Changed

- Shortened the manual status-check button label from **Check Status Now** to **Check Now**.

## 0.1.6 - 2026-08-14

### Fixed

- Added a harmless per-request cache-buster to read-only status polling so managed page caches cannot serve stale authenticated status payloads from the fixed endpoint URL.

## 0.1.5 - 2026-08-13

### Added

- Added dashboard validation and admin display support for redacted WPvivid source-activity hints while preserving strict upload-evidence warnings and the read-only protocol boundary.

## 0.1.4 - 2026-08-13

### Fixed

- Prevented Sites-tab action buttons from overflowing the table on narrower desktop widths.
- Hid revoked duplicate Sites-tab rows when a healthy active enrollment exists for the same client origin, while preserving the stored revoked record for audit/history.

### Tests

- Added focused Sites-list coverage for superseded revoked duplicate filtering.

## 0.1.3 - 2026-08-13

### Fixed

- Clarified Sites-tab manual-check availability for pending, revoked, and missing-credential rows while preserving the read-only polling model.
- Allowed active Sites-list rows with redacted stored polling-secret evidence to show **Check Status Now** without exposing encrypted credential data.

### Tests

- Added focused admin rendering coverage for credential-aware manual-check and next-poll state copy.

## 0.1.2 - 2026-08-13

### Changed

- Expanded the Sites tab to show per-source backup freshness, current package counts, latest backup/package time, and latest upload time at a glance for reported Server and WPvivid backup evidence.
- Polished the narrow Sites table layout so the accessible table caption no longer appears as a cramped visual column in stacked mobile-style rows.

### Tests

- Added focused admin rendering coverage for compact Sites-row backup source evidence and detailed source timestamp output.

## 0.1.1 - 2026-08-12

### Added

- Added dashboard-side consumption of optional redacted `backup_sources` status evidence for validation, classification, admin display, aggregate diagnostics, and protocol documentation while preserving the read-only boundary.
- Added a pending-origin lookup index for enrollment completion queries.

### Changed

- Refreshed the dashboard translation template and release-readiness documentation for the current release candidate.

### Fixed

- Allowed exact same-origin dashboard self-polling when the site hostname resolves to loopback locally, while preserving private-address rejection for other client origins.
- Treated stale or unchanged enrollment completion and local revocation writes as failures instead of reporting success.
- Cleaned enrollment failure-rate-limit transients during uninstall.

### Tests

- Hardened adversarial coverage for stale enrollment and revoke writes, transport response-size limits, and backup-source payload bounds/status enums.

## 0.1.0 - 2026-08-11

### Added

- Created the separate Alynt Drime Backups Dashboard plugin scaffold.
- Added local dashboard storage schema hooks for sites and status snapshots.
- Added an admin page shell documenting the approved v1 read-only boundary.
- Added pairing token helper primitives and initial poller scaffolding, later expanded into scheduled read-only polling.
- Added implementation plan, package metadata, and development tooling placeholders.
- Added draft v1 protocol and threat-model documents for the read-only dashboard/uploader boundary.
- Froze the Phase 3 protocol and threat-model documents as the approved implementation baseline.
- Added dashboard-side pending enrollment creation, protocol-v1 pairing-token generation, public-HTTPS origin validation, and local revocation scaffolding while keeping polling and REST enrollment disabled.
- Added credential-vault encryption/decryption and safe status-request preparation foundations without enabling REST enrollment or outbound polling.
- Added the protocol-v1 REST enrollment endpoint for authenticated client opt-in completion, encrypted polling-credential storage, and display-once credential return while still requiring a later first-poll activation slice.
- Added schema-1 status payload validation, first-poll activation, snapshot recording, and manual **Check Status Now** through the fixed read-only client status route.
- Added scheduled read-only polling with bounded batches, global/per-site locks, deterministic jitter, retry backoff, and 30-day snapshot retention cleanup.
- Added a redacted admin Diagnostics tab for scheduler state, retention defaults, polling counts, and recent safe poll outcomes.
- Added operator UI polish for Sites, Attention, Site Detail, latest redacted snapshot summaries, accessible status guidance, and support-copy diagnostics.
- Added optional structured diagnostics logging with disabled-by-default settings, redacted local event storage, recent event viewing/export, clear controls, and targeted enrollment/polling failure instrumentation.
- Added a WordPress-native responsive admin design with accessible status badges, site summaries, guided pairing, prioritized attention review, bounded recent snapshot history, safer local confirmation screens, and progressive copy/busy-state enhancements.
- Added hook, settings, FAQ, and developer documentation for the read-only dashboard boundary.
