# Alynt Drime Backups Dashboard

Read-only central monitoring dashboard for WordPress sites running Alynt Drime Backups Uploader.

This repository is the separate dashboard plugin package. The eventual host site is `control-sitesmanage` in `live-only` mode, but this scaffold makes no live changes and does not contact client sites.

## v1 Boundary

- Dashboard-generated one-time pairing token.
- Client-site opt-in before any status endpoint is enabled.
- Dashboard polling of a fixed read-only client status endpoint.
- No remote backup, restore, delete, cleanup, settings, credential, Drime token, or arbitrary command actions.

## Package Identity

- Plugin name: `Alynt Drime Backups Dashboard`
- Slug/folder: `alynt-drime-backups-dashboard`
- Main file: `alynt-drime-backups-dashboard.php`
- Text domain: `alynt-drime-backups-dashboard`
- Composer package: `alynt/alynt-drime-backups-dashboard`
- GitHub Plugin URI: `NichlasB/alynt-drime-backups-dashboard`
- PHP class prefix: `Alynt_Drime_Backups_Dashboard_`
- Function/option/action prefix: `alynt_drime_backups_dashboard_`
- Constant prefix: `ALYNT_DRIME_BACKUPS_DASHBOARD_`

## Current Status

The scaffold includes:

- WordPress plugin header and requirement gate.
- Local custom table migration hooks for dashboard-owned sites and snapshots.
- Admin page under Tools > Drime Backups Dashboard for creating local pending enrollments and displaying one-time pairing tokens.
- Protocol-v1 pairing-token helper primitives with public-HTTPS origin validation.
- Local dashboard-record revocation scaffolding that does not contact client sites.
- Credential-vault foundation for encrypted dashboard-side polling credential storage.
- Safe transport foundation that prepares the fixed read-only status request without executing outbound HTTP.
- Protocol-v1 REST enrollment endpoint for authenticated uploader opt-in completion.
- Manual **Check Status Now** for enrolled sites using the fixed authenticated read-only status route.
- Scheduled read-only polling with bounded batches, locks, jitter, retry backoff, and snapshot retention cleanup.
- Operator-focused Sites, Attention, and Site Detail views with polling evidence, latest redacted snapshot summaries, accessible status guidance, and local-only revoke/check actions.
- Redacted admin Diagnostics tab for scheduler state, retention defaults, polling counts, recent safe poll outcomes, and support-copy output.
- Implementation plan in `docs/IMPLEMENTATION_PLAN.md`.
- Draft Phase 3 protocol contract in `docs/PROTOCOL_V1.md`.
- Draft Phase 3 threat model in `docs/THREAT_MODEL_V1.md`.

Before broad implementation work, create or verify a restore point. For the new dashboard repo, use a baseline commit after this scaffold and then create an external restore point before adding enrollment, polling, or schema migrations. For companion uploader changes, run the toolkit restore-point prompt against the uploader repository first.

## Development

### Requirements

- WordPress 6.0 or higher.
- PHP 7.4 or higher.
- Composer dependencies installed for local linting and tests.
- Node.js/npm for optional asset-build and deployment helper scripts.

### Installation

1. Copy the `alynt-drime-backups-dashboard` folder to `wp-content/plugins/`.
2. Activate **Alynt Drime Backups Dashboard** from the WordPress Plugins screen.
3. Open **Tools > Drime Backups Dashboard** in WordPress admin.

### Usage

Use the dashboard to generate one-time pairing tokens, complete client-site opt-in enrollment, and monitor read-only client backup status snapshots. Version 0.1.0 does not expose remote backup, restore, delete, cleanup, settings, credential, Drime token, or arbitrary command actions.

### Changelog Summary

See `CHANGELOG.md` for the current unreleased 0.1.0 changelog and release notes.

### License

GPL-2.0-or-later. See `LICENSE`.

### Local Checks

Run PHP syntax checks before packaging:

```sh
php -l alynt-drime-backups-dashboard.php
php -l includes/class-activator.php
php -l includes/class-admin-page.php
php -l includes/class-credential-vault.php
php -l includes/class-deactivator.php
php -l includes/class-diagnostics.php
php -l includes/class-enrollment-manager.php
php -l includes/class-enrollment-rest-controller.php
php -l includes/class-origin-validator.php
php -l includes/class-pairing-tokens.php
php -l includes/class-plugin.php
php -l includes/class-poller.php
php -l includes/class-safe-transport.php
php -l includes/class-snapshot-repository.php
php -l includes/class-status-payload-validator.php
php -l includes/class-storage.php
php -l uninstall.php
```

Composer dev tooling is defined for linting and tests.

```sh
npm test
npm run lint
npm run build
```
