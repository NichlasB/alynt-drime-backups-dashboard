# AI Coding Rules

This plugin is a separate package from Alynt Drime Backups Uploader.

## Required Boundary

- Keep v1 read-only relative to client sites and Drime.
- Do not add remote backup, restore, delete, cleanup, settings, credential, Drime token, or arbitrary command actions.
- Do not make live changes to `control-sitesmanage` without explicit live-site approval.
- Require client-site opt-in before a client status endpoint can be enabled.
- Use dashboard-generated one-time pairing tokens and separate revocable polling credentials.
- Poll only a fixed, authenticated, read-only status route on enrolled client sites.

## Package Conventions

- Text domain: `alynt-drime-backups-dashboard`.
- PHP class prefix: `Alynt_Drime_Backups_Dashboard_`.
- Function, option, action, and filter prefix: `alynt_drime_backups_dashboard_`.
- Constant prefix: `ALYNT_DRIME_BACKUPS_DASHBOARD_`.
- Minimum WordPress: 6.0.
- Minimum PHP: 7.4.

## Safety

Before broad implementation work, create or verify a restore point. For companion uploader changes, run the toolkit restore-point workflow against the uploader repo before editing.
