# Settings Schema

## Alynt Drime Backups Dashboard Settings

The plugin does not register administrator-editable settings in version 0.1.0.

| Option Key | Type | Default | Sanitization | Description |
|------------|------|---------|--------------|-------------|
| `alynt_drime_backups_dashboard_schema_version` | string | none | Internal constant value | Internal database schema marker written during activation/migration. |
| `alynt_drime_backups_dashboard_diagnostics_settings` | array | disabled, minimum `warning`, 14 days, 200 events | Boolean, severity allowlist, bounded integers | Admin-controlled structured diagnostics logging settings. Stored with autoload disabled. |
| `alynt_drime_backups_dashboard_diagnostics_events` | array | empty | Structured event normalizer and context redaction before persistence | Bounded local diagnostics event ring buffer. Stored with autoload disabled. |

## Diagnostics Privacy

Structured diagnostics logging is disabled by default. When enabled, events are stored locally and redacted before persistence/export. Pairing tokens, polling secrets, authorization headers, cookies, nonces, raw payloads, raw response bodies, filesystem paths, SQL, salts, and Drime credentials must not be stored.

---

**How to maintain this file:**

- Add a row for every option registered with `register_setting()` or stored via `update_option()`.
- Update when settings are added, changed, or removed.
- Keep internal-only options clearly marked as internal.
