# Settings Schema

## Alynt Drime Backups Dashboard Settings

The plugin stores one administrator-configurable diagnostics option and two internal options in version 0.1.7. No option grants remote-action capability.

| Option Key | Type | Default | Sanitization | Tab | Description |
|------------|------|---------|--------------|-----|-------------|
| `alynt_drime_backups_dashboard_schema_version` | string | none | Internal constant value | Internal | Internal database schema marker written during activation/migration. |
| `alynt_drime_backups_dashboard_diagnostics_settings` | array | disabled, minimum `warning`, 14 days, 200 events | Boolean, severity allowlist, bounded integers | Diagnostics | Admin-controlled structured diagnostics logging settings. Stored with autoload disabled. |
| `alynt_drime_backups_dashboard_diagnostics_events` | array | empty | Structured event normalizer and context redaction before persistence | Diagnostics | Bounded local diagnostics event ring buffer. Stored with autoload disabled. |

## Diagnostics Privacy

Structured diagnostics logging is disabled by default. When enabled, events are stored locally and redacted before persistence/export. Pairing tokens, polling secrets, authorization headers, cookies, nonces, raw payloads, raw response bodies, filesystem paths, SQL, salts, and Drime credentials must not be stored.

## Related Non-Option Storage

- Custom tables store dashboard-owned site enrollment records and normalized read-only status snapshots.
- Transient locks coordinate bounded polling batches, per-site poll attempts, and bounded enrollment failure rate limits.
- Uninstall cleanup removes dashboard-owned options, transient locks, enrollment failure-rate-limit transients, scheduled hooks, and custom tables without contacting client sites.

---

**How to maintain this file:**

- Add a row for every option registered with `register_setting()` or stored via `update_option()`.
- Include the owning admin tab or mark internal storage as `Internal`.
- Update when settings are added, changed, or removed.
- Keep internal-only options clearly marked as internal.
