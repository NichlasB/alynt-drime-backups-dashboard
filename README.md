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
- Admin page shell under Tools > Drime Backups Dashboard.
- Pairing-token helper primitives.
- Poller placeholder that intentionally performs no outbound requests.
- Implementation plan in `docs/IMPLEMENTATION_PLAN.md`.
- Draft Phase 3 protocol contract in `docs/PROTOCOL_V1.md`.
- Draft Phase 3 threat model in `docs/THREAT_MODEL_V1.md`.

Before broad implementation work, create or verify a restore point. For the new dashboard repo, use a baseline commit after this scaffold and then create an external restore point before adding enrollment, polling, or schema migrations. For companion uploader changes, run the toolkit restore-point prompt against the uploader repository first.

## Development

Run PHP syntax checks before packaging:

```sh
php -l alynt-drime-backups-dashboard.php
php -l includes/class-activator.php
php -l includes/class-admin-page.php
php -l includes/class-deactivator.php
php -l includes/class-pairing-tokens.php
php -l includes/class-plugin.php
php -l includes/class-poller.php
php -l includes/class-storage.php
php -l uninstall.php
```

Composer dev tooling is defined but not installed by the scaffold.
