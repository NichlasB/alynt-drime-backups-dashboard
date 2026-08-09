<!--
Guardrail source: wp-workflow-toolkit
Guardrail template version: 1.1.0
Guardrail profile: plugin
Installed or last reconciled: 2026-08-09
Project customizations must be preserved during updates.
-->

# Alynt Drime Backups Dashboard Coding Rules

This plugin is a separate package from Alynt Drime Backups Uploader.

## Project Identity And Compatibility

- Project type: `WordPress plugin`
- Project slug: `alynt-drime-backups-dashboard`
- Text domain: `alynt-drime-backups-dashboard`
- PHP and asset prefix: `Alynt_Drime_Backups_Dashboard_`, `alynt_drime_backups_dashboard_`, and `alynt-drime-backups-dashboard`
- PHP namespace: `Not used`
- Minimum PHP: `7.4`
- Minimum WordPress: `6.0`
- Multisite support: `not declared for v1; do not assume network activation behavior until explicitly designed and tested`
- Plugin root file: `alynt-drime-backups-dashboard.php`
- Composer package: `alynt/alynt-drime-backups-dashboard`
- GitHub Plugin URI: `NichlasB/alynt-drime-backups-dashboard`

These values are compatibility contracts. Do not raise minimum versions, rename public identifiers, or change declared support without explicit approval and corresponding documentation.

## Required Boundary

- Keep v1 read-only relative to client sites and Drime.
- Do not add remote backup, restore, delete, cleanup, settings, credential, Drime token, or arbitrary command actions.
- Do not make live changes to `control-sitesmanage` without explicit live-site approval.
- Require client-site opt-in before a client status endpoint can be enabled.
- Use dashboard-generated one-time pairing tokens and separate revocable polling credentials.
- Poll only a fixed, authenticated, read-only status route on enrolled client sites.

## Explicit Non-Goals

- No remote backup creation.
- No remote restore or restore preparation.
- No remote delete, cleanup, retention, or Drime mutation.
- No remote settings mutation on client sites.
- No arbitrary health checks against user-entered URLs.
- No storage of client Drime API tokens in the dashboard.
- No live `control-sitesmanage` changes without the live-site approval gate.

## Architecture And Scope

- Follow the existing simple global-class architecture unless an approved plan changes it.
- Keep the root file focused on metadata, constants, bootstrap, and lifecycle hooks.
- Keep files and components focused on cohesive responsibilities.
- Prefer WordPress APIs and established project abstractions over parallel frameworks.
- Preserve public hooks, REST routes, AJAX actions, cron events, option names, table names, and extension points unless an approved migration or deprecation plan exists.
- Avoid opportunistic refactors outside the requested behavior.
- Do not add abstractions merely to make a small change look more architectural.
- Do not edit generated, vendored, dependency, minified, cache, release, or export output as source.

## Storage And Public Contracts

- Dashboard REST namespace: `alynt-drime-backups-dashboard/v1`.
- Client REST namespace expected from uploader: `alynt-drime-backups-uploader/v1`.
- Dashboard sites table: `{$wpdb->prefix}alynt_drime_dashboard_sites`.
- Dashboard snapshots table: `{$wpdb->prefix}alynt_drime_dashboard_snapshots`.
- Schema option: `alynt_drime_backups_dashboard_schema_version`.
- Polling cron hook: `alynt_drime_backups_dashboard_poll_sites`.
- Admin menu slug: `alynt-drime-backups-dashboard`.
- Pairing token prefix: `adb1.`.

Storage identifiers are durable contracts after first release. Migration, rename, or cleanup behavior requires an approved plan and tests.

## Package Conventions

- Text domain: `alynt-drime-backups-dashboard`.
- PHP class prefix: `Alynt_Drime_Backups_Dashboard_`.
- Function, option, action, and filter prefix: `alynt_drime_backups_dashboard_`.
- Constant prefix: `ALYNT_DRIME_BACKUPS_DASHBOARD_`.
- Minimum WordPress: 6.0.
- Minimum PHP: 7.4.
- Exclude development-only guardrail files from the plugin ZIP.

## WordPress Security

- Treat request data, uncertain stored data, remote responses, file contents, imported configuration, and user-controlled markup as untrusted.
- Unslash WordPress request data before validation or sanitization where applicable.
- Validate structural and business constraints; sanitize for storage; escape for the final output context.
- Authorize sensitive operations with the narrowest capability and ownership or site-boundary check.
- Use nonces for request intent, never as authorization.
- Give REST routes explicit `permission_callback` functions and validate route arguments.
- Use prepared SQL values and allowlist dynamic identifiers.
- Protect file operations, redirects, remote requests, previews, imports, exports, and template rendering against their relevant abuse cases.
- Never persist or log passwords, secrets, raw tokens, cookies, authorization headers, or unnecessary personal data.

## Site Operations Boundary

- Repository instructions do not authorize LocalWP, staging, or live-site work.
- In the AI Workflows environment, read `Site Operations/wordpress-site-operations.md` and the local `Site Operations/wordpress-sites.md` registry before site-specific work.
- Require a confirmed site key and mode: `local-first`, `local-only`, or `live-only`.
- Confirm the resolved LocalWP and/or live targets before edits, database writes, deployment, or service changes.
- Check for Novamira MCP before substantive LocalWP work and use it when it is available and safer.
- For direct LocalWP SQL on Windows, resolve the current site metadata and use LocalWP's bundled `mysql.exe`; do not start with `wp-load.php` or a generic PHP MySQL connection.
- Require a read-only preview before destructive LocalWP SQL and explicit approval before every live write or deployment.

## Accessibility, Internationalization, And Frontend Safety

- Use semantic markup, accessible names, keyboard operation, visible focus, meaningful error association, and status announcements where required.
- Escape late for the actual HTML, attribute, URL, JSON, JavaScript, or allowed-markup context.
- Keep user-facing strings translatable and preserve translator context.
- Namespace CSS classes, handles, DOM events, browser storage keys, hooks, and PHP symbols.
- Avoid broad selectors and globals that leak into WordPress admin, parent themes, plugins, or unrelated frontend regions.

## Test Integrity And Verification

- Test observable contracts and failure behavior at the layer that can prove them.
- Prefer red-to-green regression evidence for confirmed defects when practical.
- Do not remove assertions, add skips, lower thresholds, replace integration evidence with mocks, or alter expected output solely to make checks pass.
- Treat static analysis, unit tests, browser checks, and runtime checks as different evidence.
- Run documented commands and distinguish executed evidence from assumptions.

## Project Workflow

- Canonical QA: `Not available yet - run PHP syntax checks and focused tests until tooling is installed`.
- Lint/static analysis: `php ./vendor/bin/phpcs` after Composer dev dependencies are installed.
- Automated tests: `php ./vendor/bin/phpunit` after Composer dev dependencies are installed.
- Production build: `Not applicable yet`.
- Runtime acceptance: `clean activation/deactivation/uninstall in an isolated WordPress environment before release`.
- Maximum autonomous agent risk tier: `R2`.

## Plugin-Specific Risk Triggers

Treat authentication, authorization, personal data, migrations, uninstall, file operations, remote requests, concurrency, and public API changes as `R3` unless project policy is stricter.

## Safety

Before broad implementation work, create or verify a restore point. For companion uploader changes, run the toolkit restore-point workflow against the uploader repo before editing.
