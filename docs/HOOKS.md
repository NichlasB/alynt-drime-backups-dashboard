# Hook Reference

## Alynt Drime Backups Dashboard Hooks

Alynt Drime Backups Dashboard does not expose public custom extension actions or filters in version 0.1.15.

The hooks below are internal WordPress integration points owned by the plugin. Treat them as implementation details unless a future release explicitly documents them as public extension points.

## Public Extension Hooks

None in version 0.1.15.

## WordPress Lifecycle Hooks

### Activation

Registered from `alynt-drime-backups-dashboard.php`.

Runs `Alynt_Drime_Backups_Dashboard_Activator::activate()` to install or upgrade local dashboard-owned tables, store the schema version marker, and schedule the read-only polling and local snapshot cleanup events.

### Deactivation

Registered from `alynt-drime-backups-dashboard.php`.

Runs `Alynt_Drime_Backups_Dashboard_Deactivator::deactivate()` to unschedule dashboard polling and local snapshot cleanup events. Deactivation does not contact client sites and does not delete stored dashboard records.

### Uninstall

Handled by `uninstall.php`. WordPress-discovered copies outside the canonical `alynt-drime-backups-dashboard` folder exit without affecting dashboard state. A canonical uninstall clears scheduled events, transient locks, and enrollment failure-rate-limit transients but preserves monitoring records, snapshots, encrypted polling credentials, and dashboard options by default. Permanent dashboard-data removal requires the explicit `ALYNT_DRIME_BACKUPS_DASHBOARD_PURGE_DATA_ON_UNINSTALL` constant in `wp-config.php`. It does not contact client sites.

## Admin and Runtime Hooks

| Hook | Type | Owner | Purpose |
|------|------|-------|---------|
| `plugins_loaded` | Action | WordPress runtime | Instantiates the plugin after requirements pass. |
| `init` | Action | WordPress runtime | Loads the plugin text domain at priority `0`. |
| `admin_notices` | Action | WordPress admin | Shows requirement-gate notices when minimum PHP or WordPress requirements are not met. |
| `admin_menu` | Action | WordPress admin | Registers the Tools > Drime Backups Dashboard admin page. |
| `admin_enqueue_scripts` | Action | WordPress admin | Enqueues dashboard assets only on the dashboard screen. |
| `rest_api_init` | Action | WordPress REST API | Registers the enrollment completion endpoint. |
| `cron_schedules` | Filter | WordPress cron | Adds the dashboard polling recurrence. |

## Scheduled Event Hooks

| Hook | Recurrence | Purpose |
|------|------------|---------|
| `alynt_drime_backups_dashboard_poll_sites` | `alynt_drime_backups_dashboard_15_minutes` | Runs bounded read-only polling batches for enrolled client sites. |
| `alynt_drime_backups_dashboard_cleanup_snapshots` | `daily` | Removes old local dashboard snapshot rows according to retention rules. |

Polling uses the fixed authenticated client status route and stores normalized local snapshots. It must not trigger remote backup creation, restore, delete, cleanup, settings, credential, Drime-token, or arbitrary-command actions. V2.1 Request Backup Now uses a separate client opt-in and signed action-intent route; it is not authorized by polling credentials.

## REST Route

| Route | Method | Purpose |
|-------|--------|---------|
| `/wp-json/alynt-drime-backups-dashboard/v1/enroll` | `POST` | Completes client-site opt-in enrollment using a dashboard-generated pairing token. |

The enrollment route validates the one-time pairing token, expected client origin, protocol version, and status schema version before storing encrypted dashboard-side polling credentials. It does not enable V2.1 actions; those require a separate `adb2a` action opt-in token after V1 pairing.

## Maintenance Rules

- Add new public extension hooks here before release if they are intentionally supported for third-party use.
- Keep internal hooks clearly labeled as internal implementation details.
- Do not document secrets, raw tokens, authorization headers, cookies, nonces, salts, filesystem paths, SQL, raw payloads, raw response bodies, or Drime credentials.
- Preserve the V1 read-only boundary. V2.1 action behavior must remain limited to separately opted-in signed `scan_upload_now` intents unless a later release has an explicitly approved architecture change.
