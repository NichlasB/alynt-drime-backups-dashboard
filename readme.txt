=== Alynt Drime Backups Dashboard ===
Contributors: alynt
Tags: backups, monitoring, dashboard
Requires at least: 6.0
Tested up to: 6.0
Requires PHP: 7.4
Stable tag: 0.1.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Read-only central monitoring dashboard for Alynt Drime backup uploader sites.

== Description ==

Alynt Drime Backups Dashboard is planned as a read-only central status dashboard for client sites running Alynt Drime Backups Uploader.

Version 0.1.0 is a local scaffold with pending-enrollment token generation, REST enrollment completion, credential-vault primitives, safe status-request preparation, first-poll activation, manual read-only status checks, scheduled read-only polling, bounded status-history retention, operator-focused admin views, redacted support diagnostics, and optional structured diagnostics logging that is disabled by default. It does not expose remote actions or make live changes.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins/alynt-drime-backups-dashboard`.
2. Activate Alynt Drime Backups Dashboard from the WordPress Plugins screen.
3. Open Tools > Drime Backups Dashboard.

== Frequently Asked Questions ==

= Can the dashboard run backups, restores, or cleanup on client sites? =

No. Version 0.1.0 is read-only. It can generate dashboard-owned pairing tokens, accept client opt-in enrollment, poll a fixed authenticated status endpoint, and store local status snapshots. It cannot trigger remote backup, restore, delete, cleanup, settings, credential, Drime-token, or arbitrary command actions.

= What happens when I generate a pairing token? =

The dashboard creates a pending local enrollment for the expected client origin and displays a one-time pairing credential. The client site must opt in by submitting the enrollment payload back to the dashboard REST endpoint before the dashboard can poll status.

= Does diagnostics logging store secrets? =

No. Diagnostics logging is disabled by default and redacts sensitive fields before local persistence or export. Pairing tokens, polling secrets, authorization headers, cookies, nonces, raw payloads, raw response bodies, filesystem paths, SQL, salts, and Drime credentials must not be stored.

== Developer Notes ==

See `docs/IMPLEMENTATION_PLAN.md` for the implementation sequence, `docs/PROTOCOL_V1.md` for the read-only dashboard/uploader contract, `docs/THREAT_MODEL_V1.md` for the security model, `docs/SETTINGS.md` for stored options, and `docs/HOOKS.md` for hook ownership.

== Changelog ==

= 0.1.1 =
* Allowed exact same-origin dashboard self-polling when the site hostname resolves to loopback locally, while preserving private-address rejection for other client origins.

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
