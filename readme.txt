=== Alynt Drime Backups Dashboard ===
Contributors: alynt
Tags: backups, monitoring, dashboard
Requires at least: 6.0
Tested up to: 6.0
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Read-only central monitoring dashboard for Alynt Drime backup uploader sites.

== Description ==

Alynt Drime Backups Dashboard is planned as a read-only central status dashboard for client sites running Alynt Drime Backups Uploader.

Version 0.1.0 is a local scaffold with pending-enrollment token generation, REST enrollment completion, credential-vault primitives, safe status-request preparation, first-poll activation, and manual read-only status checks. It does not schedule background polling, expose remote actions, or make live changes.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins/alynt-drime-backups-dashboard`.
2. Activate Alynt Drime Backups Dashboard from the WordPress Plugins screen.
3. Open Tools > Drime Backups Dashboard.

== Changelog ==

= 0.1.0 =
* Initial local scaffold.
* Added local pending enrollment and protocol-v1 pairing token scaffolding.
* Added encrypted credential-vault and safe transport foundations without enabling polling.
* Added authenticated protocol-v1 REST enrollment completion while keeping first-poll activation separate.
* Added schema-1 status validation, first-poll activation, snapshot recording, and manual Check Status Now.
