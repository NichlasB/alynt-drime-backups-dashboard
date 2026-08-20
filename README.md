# Alynt Drime Backups Dashboard

Read-only central monitoring dashboard for WordPress sites running Alynt Drime Backups Uploader.

This repository is the separate dashboard plugin package. The current dashboard host is `control-sitesmanage` in `live-only` mode. The plugin remains read-only relative to client sites and Drime.

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

Version 0.1.14 currently includes:

- WordPress plugin header and requirement gate.
- Local custom table migration hooks for dashboard-owned sites and snapshots.
- Admin page under Tools > Drime Backups Dashboard for creating local pending enrollments and displaying one-time pairing tokens.
- Protocol-v1 pairing-token helper primitives with public-HTTPS origin validation.
- Local dashboard-record revocation scaffolding that does not contact client sites.
- Credential-vault foundation for encrypted dashboard-side polling credential storage.
- Safe transport foundation that prepares the fixed read-only status request without executing outbound HTTP.
- Protocol-v1 REST enrollment endpoint for authenticated uploader opt-in completion.
- Manual **Check Now** for enrolled sites using the fixed authenticated read-only status route.
- Scheduled read-only polling with bounded batches, locks, jitter, retry backoff, and snapshot retention cleanup.
- Responsive, WordPress-native Sites, Attention, and Site Detail views with status summaries, polling evidence, accessible status badges, bounded recent snapshot history, and local-only revoke/check actions.
- Redacted admin Diagnostics tab for scheduler state, retention defaults, polling counts, recent safe poll outcomes, and progressively enhanced support-copy/export controls.
- Optional structured diagnostics logging, disabled by default, with a bounded redacted local event buffer for support troubleshooting.
- Always-on, bounded, redacted operator action history for dashboard-local actions such as pairing-token creation, local revocation, manual **Check Now**, and diagnostics changes.
- Optional dashboard-side display of redacted per-source backup freshness and inventory evidence from schema-1 uploader status payloads.
- Redacted WPvivid source-activity hints that distinguish local WPvivid activity evidence from Alynt upload proof.
- At-a-glance Sites-row source summaries for Server and WPvivid freshness, current package counts, latest backup/package time, and latest upload time when clients report that evidence.
- Credential-aware Sites-tab manual-check state copy for active, pending, revoked, and missing-credential rows.
- Sites-tab layout protection for action buttons and hiding of superseded revoked duplicate rows when a healthy active enrollment exists for the same origin.
- Harmless per-request cache-busting for read-only status polling so managed page caches cannot serve stale authenticated status payloads.
- Implementation plan in `docs/IMPLEMENTATION_PLAN.md`.
- Approved Phase 3 protocol contract in `docs/PROTOCOL_V1.md`.
- Approved Phase 3 threat model in `docs/THREAT_MODEL_V1.md`.
- Future v2 remote-actions planning in `docs/V2_REMOTE_ACTIONS_PLAN.md`.
- Settings reference in `docs/SETTINGS.md`.
- Hook reference in `docs/HOOKS.md`.

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

Use the dashboard to generate one-time pairing tokens, complete client-site opt-in enrollment, and monitor read-only client backup status snapshots. Version 0.1.14 does not expose remote backup, restore, delete, cleanup, settings, credential, Drime token, or arbitrary command actions.

Diagnostics live under **Tools > Drime Backups Dashboard > Diagnostics**. Structured diagnostics logging is disabled by default. When an administrator explicitly enables it, the plugin stores a bounded local event buffer with redaction applied before persistence/export. Pairing tokens, polling secrets, authorization headers, cookies, nonces, raw payloads, raw response bodies, filesystem paths, SQL, salts, and Drime credentials are not stored in diagnostics events.

### FAQ

#### Can the dashboard run backups, restores, or cleanup on client sites?

No. Version 0.1.14 is read-only. It can generate dashboard-owned pairing tokens, accept client opt-in enrollment, poll a fixed authenticated status endpoint, and store local status snapshots. It cannot trigger remote backup, restore, delete, cleanup, settings, credential, Drime-token, or arbitrary command actions.

#### What happens when I generate a pairing token?

The dashboard creates a pending local enrollment for the expected client origin and displays a one-time pairing credential. The client site must opt in by submitting the enrollment payload back to the dashboard REST endpoint before the dashboard can poll status.

#### Does diagnostics logging store secrets?

No. Diagnostics logging is disabled by default and redacts sensitive fields before local persistence or export. Pairing tokens, polling secrets, authorization headers, cookies, nonces, raw payloads, raw response bodies, filesystem paths, SQL, salts, and Drime credentials must not be stored.

#### Where are implementation details documented?

See `docs/IMPLEMENTATION_PLAN.md` for the implementation sequence, `docs/PROTOCOL_V1.md` for the read-only dashboard/uploader contract, `docs/THREAT_MODEL_V1.md` for the security model, `docs/V2_REMOTE_ACTIONS_PLAN.md` for future remote-operation planning, `docs/SETTINGS.md` for stored options, and `docs/HOOKS.md` for hook ownership.

### Changelog Summary

See `CHANGELOG.md` for the current unreleased changelog and release notes.

### License

GPL-2.0-or-later. See `LICENSE`.

### Local Checks

Run the configured checks before packaging:

```sh
npm test
npm run lint
npm run build
```

For targeted PHP syntax checks during development, run `php -l` against changed PHP files. The configured lint script covers the committed PHP paths.

## Release Packaging

Alynt Plugin Updater distribution uses GitHub release assets from `NichlasB/alynt-drime-backups-dashboard`.

- Release tags use `vX.Y.Z`.
- The release workflow packages `alynt-drime-backups-dashboard-X.Y.Z.zip`.
- The ZIP top-level folder is `alynt-drime-backups-dashboard/`.
- CI builds runtime assets with `npm ci` and `npm run build`.
- Composer dependencies are development-only; release packages exclude `vendor/`.
- Release packages exclude test suites, source assets, build scripts, Composer/npm manifests, local deployment helpers, and internal engineering docs.
- Release publication, release-asset validation, and WordPress updater runtime acceptance remain separate approval-gated workflows.
