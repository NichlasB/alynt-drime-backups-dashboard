# Alynt Drime Backups Dashboard

Central WordPress admin dashboard for monitoring sites that run **Alynt Drime Backups Uploader**.

The dashboard is intentionally conservative: its core job is to show whether enrolled sites are reporting backup health, backup evidence, source freshness, action history, diagnostics, and restore-readiness evidence without giving the dashboard broad control over client sites or Drime.

## What this plugin does

- Creates local dashboard records for client sites.
- Generates one-time pairing tokens for explicit client-site opt-in.
- Polls enrolled clients through a fixed authenticated read-only status endpoint.
- Shows fleet-level backup status on the Sites and Attention tabs.
- Shows detailed per-site evidence on Site Detail screens.
- Tracks redacted status snapshots, source freshness, inventory counts, and action history.
- Provides support-safe Diagnostics summaries and export/download controls.
- Displays optional restore-readiness evidence when clients report sanitized source-level metadata.

## Safety boundaries

The dashboard must stay narrow by design.

It does **not**:

- store client Drime API credentials;
- browse arbitrary filesystem paths;
- expose raw backup package paths, filenames, candidate references, or Drime identifiers;
- create fresh WPvivid or server-runner backups;
- restore data;
- delete backups;
- clean up local or Drime data;
- run arbitrary commands;
- mutate client settings without a separately designed and approved capability.

Version 1 monitoring remains read-only relative to client sites and Drime. Later V2 capabilities are opt-in, signed, bounded, client-validated, and documented separately.

## Current capability areas

### Monitoring

- Sites, Attention, and Site Detail screens.
- Manual **Check Now** for enrolled sites.
- Scheduled read-only polling with bounded batches, locks, jitter, retry backoff, and local retention cleanup.
- Local-only dashboard actions such as revoke, archive/unarchive, and pause/resume polling.

### Backup evidence

- Compact Sites-row backup-health summaries.
- Server runner and WPvivid source summaries.
- Freshness windows, latest activity, upload evidence, package counts, queued counts, and warning states when clients report them.
- Dashboard-owned WPvivid external/optional policy for sites where WPvivid is intentionally managed outside Alynt-uploaded evidence.

### Diagnostics

- Scheduler state and polling health.
- Record-state counts.
- Attention/recovery aggregate evidence.
- Restore-readiness aggregate evidence.
- Runtime/version contract panel.
- Redacted support-copy JSON and download controls.

### V2 remote-action visibility

The dashboard can show and reconcile redacted action history for supported V2 clients.

Currently implemented action areas include:

- **Request Backup Now**: sends a signed `scan_upload_now` intent after separate client-side action opt-in. The client scans for ready packages and uploads eligible items using its own existing settings and Drime credentials.
- **Schedule Preview / Apply**: limited to the Alynt uploader scan cadence, guarded by fresh preview evidence and separate client-side opt-in.
- **Schedule Rollback Preview**: non-mutating support evidence only. It does not execute rollback or change schedules.
- **Cleanup Preview**: non-mutating evidence only. Cleanup apply remains unavailable.

These actions do not grant general remote control.

### Restore-readiness evidence

When clients report optional sanitized restore-readiness metadata, the dashboard can display:

- Site Detail restore-readiness evidence;
- compact Sites-row restore evidence hints;
- support-safe Diagnostics aggregate counts;
- source-level candidate totals for Server runner and WPvivid.

This is evidence only. It does not download, stage, unpack, import, restore, delete, or guarantee recoverability.

## Typical operator flow

1. Install and activate the dashboard plugin.
2. Create a pending dashboard record for a client origin.
3. Copy the one-time V1 pairing token.
4. Paste the token on the client uploader site to opt in.
5. Wait for the first valid read-only poll.
6. Monitor backup health from the Sites and Attention tabs.
7. Use Site Detail and Diagnostics when a site needs investigation.
8. For V2-capable clients, generate separate action opt-in tokens only when that bounded capability is needed.

## Installation

1. Copy the `alynt-drime-backups-dashboard` folder to `wp-content/plugins/`.
2. Activate **Alynt Drime Backups Dashboard** from the WordPress Plugins screen.
3. Open **Tools > Drime Backups Dashboard** in WordPress admin.

## Requirements

- WordPress 6.0 or higher.
- PHP 7.4 or higher.
- Composer dependencies installed for local linting and tests.
- Node.js/npm for asset-build and release helper scripts.

## Package identity

| Item | Value |
| --- | --- |
| Plugin name | `Alynt Drime Backups Dashboard` |
| Slug/folder | `alynt-drime-backups-dashboard` |
| Main file | `alynt-drime-backups-dashboard.php` |
| Text domain | `alynt-drime-backups-dashboard` |
| Composer package | `alynt/alynt-drime-backups-dashboard` |
| GitHub Plugin URI | `NichlasB/alynt-drime-backups-dashboard` |
| PHP class prefix | `Alynt_Drime_Backups_Dashboard_` |
| Function/option/action prefix | `alynt_drime_backups_dashboard_` |
| Constant prefix | `ALYNT_DRIME_BACKUPS_DASHBOARD_` |

## Development

Run the configured checks before packaging:

```sh
npm test
npm run lint
npm run build
```

For targeted PHP syntax checks during development, run `php -l` against changed PHP files. The configured lint script covers the committed PHP paths.

## Release packaging

Alynt Plugin Updater distribution uses GitHub release assets from `NichlasB/alynt-drime-backups-dashboard`.

- Release tags use `vX.Y.Z`.
- Release assets are named `alynt-drime-backups-dashboard-X.Y.Z.zip`.
- The ZIP top-level folder is `alynt-drime-backups-dashboard/`.
- CI builds runtime assets with `npm ci` and `npm run build`.
- Composer dependencies are development-only; release packages exclude `vendor/`.
- Release packages exclude test suites, source assets, build scripts, Composer/npm manifests, local deployment helpers, and internal engineering docs.
- Release publication, release-asset validation, and WordPress updater runtime acceptance remain separate approval-gated workflows.

## Documentation map

- `CHANGELOG.md` — release history and current release notes.
- `docs/IMPLEMENTATION_PLAN.md` — implementation roadmap and completed slices.
- `docs/PROTOCOL_V1.md` — read-only dashboard/uploader pairing and polling contract.
- `docs/THREAT_MODEL_V1.md` — V1 monitoring threat model.
- `docs/PROTOCOL_V2.md` — signed V2 action protocol.
- `docs/THREAT_MODEL_V2.md` — V2 action threat model.
- `docs/V2_REMOTE_ACTIONS_PLAN.md` — future remote-operation planning.
- `docs/SETTINGS.md` — stored options and configuration ownership.
- `docs/HOOKS.md` — hook ownership and extension-point notes.

## FAQ

### Can the dashboard run backups, restores, or cleanup on client sites?

Not as a general remote-control system. V1 is read-only monitoring. V2.1 adds only a bounded **Request Backup Now** action after separate client-side opt-in; that asks the client uploader to scan for ready packages and upload eligible items. It does not create fresh WPvivid/server-runner backups, restore, delete, clean up, change settings, expose Drime credentials, or run arbitrary commands.

### What happens when I generate a pairing token?

The dashboard creates a pending local enrollment for the expected client origin and displays a one-time pairing credential. The client site must opt in by submitting the enrollment payload back to the dashboard REST endpoint before the dashboard can poll status.

### Does diagnostics logging store secrets?

No. Diagnostics logging is disabled by default and redacts sensitive fields before local persistence or export. Pairing tokens, polling secrets, authorization headers, cookies, nonces, raw payloads, raw response bodies, filesystem paths, SQL, salts, and Drime credentials must not be stored.

### Where are detailed release notes?

Use `CHANGELOG.md`. The README is intentionally kept as a short landing page rather than a release-history archive.

## License

GPL-2.0-or-later. See `LICENSE`.
