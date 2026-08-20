# Settings Schema

## Alynt Drime Backups Dashboard Settings

The plugin stores one administrator-configurable diagnostics option and three internal options. V2.1 action capability is not granted by an option alone; it requires an enrolled site row with a stored encrypted action signing key plus client-reported opt-in capability.

| Option Key | Type | Default | Sanitization | Tab | Description |
|------------|------|---------|--------------|-----|-------------|
| `alynt_drime_backups_dashboard_schema_version` | string | none | Internal constant value | Internal | Internal database schema marker written during activation/migration. |
| `alynt_drime_backups_dashboard_diagnostics_settings` | array | disabled, minimum `warning`, 14 days, 200 events | Boolean, severity allowlist, bounded integers | Diagnostics | Admin-controlled structured diagnostics logging settings. Stored with autoload disabled. |
| `alynt_drime_backups_dashboard_diagnostics_events` | array | empty | Structured event normalizer and context redaction before persistence | Diagnostics | Bounded local diagnostics event ring buffer. Stored with autoload disabled. |
| `alynt_drime_backups_dashboard_audit_events` | array | empty | Operator-action normalizer and context redaction before persistence | Diagnostics | Always-on bounded local operator action history for dashboard-local actions. Stored with autoload disabled. |

## Diagnostics Privacy

Structured diagnostics logging is disabled by default. When enabled, events are stored locally and redacted before persistence/export. Pairing tokens, polling secrets, authorization headers, cookies, nonces, raw payloads, raw response bodies, filesystem paths, SQL, salts, and Drime credentials must not be stored.

Operator action history is stored separately from optional diagnostics logging. It is always-on, bounded to dashboard-local actions, and redacts context before persistence/export. It must not store pairing tokens, polling secrets, raw response bodies, filesystem paths, Drime identifiers beyond approved redacted payload fields, or Drime credentials.

## Related Non-Option Storage

- Custom tables store dashboard-owned site enrollment records, normalized read-only status snapshots, and bounded V2.1 remote-action request/history records.
- Transient locks coordinate bounded polling batches, per-site poll attempts, and bounded enrollment failure rate limits.
- Uninstall cleanup removes transient locks, enrollment failure-rate-limit transients, and scheduled hooks without contacting client sites. Dashboard-owned tables and options are preserved by default because they contain the dashboard-side enrollment, polling, and action-history state. A permanent purge requires the explicit `ALYNT_DRIME_BACKUPS_DASHBOARD_PURGE_DATA_ON_UNINSTALL` constant in `wp-config.php`; copied rollback directories always exit before cleanup.

---

**How to maintain this file:**

- Add a row for every option registered with `register_setting()` or stored via `update_option()`.
- Include the owning admin tab or mark internal storage as `Internal`.
- Update when settings are added, changed, or removed.
- Keep internal-only options clearly marked as internal.
