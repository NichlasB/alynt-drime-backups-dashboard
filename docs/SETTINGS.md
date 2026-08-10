# Settings Schema

## Alynt Drime Backups Dashboard Settings

The plugin does not register administrator-editable settings in version 0.1.0.

| Option Key | Type | Default | Sanitization | Description |
|------------|------|---------|--------------|-------------|
| `alynt_drime_backups_dashboard_schema_version` | string | none | Internal constant value | Internal database schema marker written during activation/migration. |

---

**How to maintain this file:**

- Add a row for every option registered with `register_setting()` or stored via `update_option()`.
- Update when settings are added, changed, or removed.
- Keep internal-only options clearly marked as internal.
