=== Alynt Drime Backups Dashboard ===
Contributors: alynt
Tags: backups, monitoring, dashboard
Requires at least: 6.0
Tested up to: 6.0
Requires PHP: 7.4
Stable tag: 0.1.13
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Read-only central monitoring dashboard for Alynt Drime backup uploader sites.

== Description ==

Alynt Drime Backups Dashboard is planned as a read-only central status dashboard for client sites running Alynt Drime Backups Uploader.

Version 0.1.13 is a local scaffold with pending-enrollment token generation, REST enrollment completion, credential-vault primitives, safe status-request preparation, first-poll activation, manual read-only status checks, scheduled read-only polling, bounded status-history retention, operator-focused admin views, redacted support diagnostics, optional structured diagnostics logging that is disabled by default, always-on redacted operator action history for dashboard-local actions, optional dashboard-side display of redacted per-source backup freshness evidence, redacted WPvivid source-activity hints, dashboard-side WPvivid freshness policy for weekly/biweekly schedules, schedule-aware WPvivid freshness-policy ingestion, clearer Sites-tab manual-check state copy, improved Sites-tab handling for action-button width and superseded revoked duplicates, stale-cache protection for read-only status polling, shorter manual-check button wording, aligned Sites-tab manual-check helper copy, copy-control busy-state polish, timestamp fallback hardening, malformed snapshot fail-closed behavior, and v2 remote-action planning documentation. It does not expose remote actions or make live changes.

The current development tree can also show optional redacted per-source backup freshness, current package counts, latest backup/package time, and latest upload time directly on the Sites tab when schema-1 uploader payloads report that evidence.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins/alynt-drime-backups-dashboard`.
2. Activate Alynt Drime Backups Dashboard from the WordPress Plugins screen.
3. Open Tools > Drime Backups Dashboard.

== Frequently Asked Questions ==

= Can the dashboard run backups, restores, or cleanup on client sites? =

No. Version 0.1.13 is read-only. It can generate dashboard-owned pairing tokens, accept client opt-in enrollment, poll a fixed authenticated status endpoint, and store local status snapshots. It cannot trigger remote backup, restore, delete, cleanup, settings, credential, Drime-token, or arbitrary command actions.

= What happens when I generate a pairing token? =

The dashboard creates a pending local enrollment for the expected client origin and displays a one-time pairing credential. The client site must opt in by submitting the enrollment payload back to the dashboard REST endpoint before the dashboard can poll status.

= Does diagnostics logging store secrets? =

No. Diagnostics logging is disabled by default and redacts sensitive fields before local persistence or export. Pairing tokens, polling secrets, authorization headers, cookies, nonces, raw payloads, raw response bodies, filesystem paths, SQL, salts, and Drime credentials must not be stored.

== Developer Notes ==

See `docs/IMPLEMENTATION_PLAN.md` for the implementation sequence, `docs/PROTOCOL_V1.md` for the read-only dashboard/uploader contract, `docs/THREAT_MODEL_V1.md` for the security model, `docs/SETTINGS.md` for stored options, and `docs/HOOKS.md` for hook ownership.

== Changelog ==

= 0.1.13 =
* Added an always-on, bounded, redacted operator action history for dashboard-local actions such as pairing-token creation, local revocation, manual Check Now, and diagnostics changes.

= 0.1.12 =
* Allowed dashboard ingestion of WPvivid Pro/addon schedule policy basis labels reported by live uploader sites.

= 0.1.11 =
* Added dashboard ingestion, classification, and display support for uploader-reported WPvivid schedule policy summaries.

= 0.1.10 =
* Added a dashboard-side WPvivid freshness policy so weekly or biweekly WPvivid backup evidence can remain healthy while server-runner evidence stays strictly monitored.

= 0.1.9 =
* Aligned the Sites-tab helper copy with the shortened Check Now manual status-check button label.

= 0.1.8 =
* Added accessible busy-state feedback for pairing-token, diagnostics-event, and support-summary copy buttons.
* Added explicit WordPress date/time format fallbacks for admin timestamp rendering.
* Added v2 remote-action planning documentation while preserving the v1 read-only boundary.
* Treated malformed or empty stored status snapshot payloads as not reporting instead of falling through to a misleading configuration state.
* Added focused status-classifier regression coverage for malformed JSON and empty decoded snapshot payloads.

= 0.1.7 =
* Shortened the manual status-check button label from Check Status Now to Check Now.

= 0.1.6 =
* Added a harmless per-request cache-buster to read-only status polling so managed page caches cannot serve stale authenticated status payloads from the fixed endpoint URL.

= 0.1.5 =
* Added dashboard validation and admin display support for redacted WPvivid source-activity hints while preserving strict upload-evidence warnings and the read-only protocol boundary.

= 0.1.4 =
* Prevented Sites-tab action buttons from overflowing the table on narrower desktop widths.
* Hid revoked duplicate Sites-tab rows when a healthy active enrollment exists for the same client origin.
* Added focused Sites-list tests for superseded revoked duplicate filtering.

= 0.1.3 =
* Clarified Sites-tab manual-check availability for pending, revoked, and missing-credential rows.
* Allowed active Sites-list rows with redacted stored polling-secret evidence to show Check Status Now without exposing encrypted credential data.
* Added focused admin rendering tests for credential-aware manual-check and next-poll state copy.

= 0.1.2 =
* Expanded the Sites tab with at-a-glance per-source backup freshness, current package counts, latest backup/package time, and latest upload time for reported Server and WPvivid evidence.
* Polished the narrow Sites table layout so the accessible table caption does not appear as a cramped visual column in stacked rows.
* Added focused admin rendering tests for compact Sites-row backup source evidence.

= 0.1.1 =
* Added dashboard-side consumption of optional redacted backup source freshness evidence while preserving the read-only boundary.
* Allowed exact same-origin dashboard self-polling when the site hostname resolves to loopback locally, while preserving private-address rejection for other client origins.
* Added a pending-origin lookup index for enrollment completion queries.
* Treated stale or unchanged enrollment completion and local revocation writes as failures.
* Cleaned enrollment failure-rate-limit transients during uninstall.
* Refreshed translation and documentation artifacts for the current release candidate.

= 0.1.0 =
* Initial local scaffold.
* Added local pending enrollment and protocol-v1 pairing token scaffolding.
* Added encrypted credential-vault and safe transport foundations without enabling polling.
* Added authenticated protocol-v1 REST enrollment completion while keeping first-poll activation separate.
* Added schema-1 status validation, first-poll activation, snapshot recording, and manual Check Status Now.
* Added scheduled read-only polling with bounded batches, locks, jitter, retry backoff, and retention cleanup.
* Added redacted admin Diagnostics for scheduler state, retention defaults, polling counts, and recent safe poll outcomes.
* Added operator UI polish for Sites, Attention, Site Detail, latest redacted snapshot summaries, accessible status guidance, and support-copy diagnostics.
* Added optional structured diagnostics logging with admin-only settings, redacted event viewing/export, and local clear controls.
* Added hook, settings, FAQ, and developer documentation for the read-only dashboard boundary.
