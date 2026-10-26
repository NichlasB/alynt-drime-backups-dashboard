# Isolated Dashboard Lifecycle/Data-Preservation Owned Proof — 2026-10-26

## Authority, identity and containment

User-approved R3 proof and fresh exact-target/fixture confirmation: `plugin-tester`,
`local-only`, LocalWP “Plugin Tester”, `http://plugin-tester.local`, WordPress at
`C:\Users\Captain\Local Sites\plugin-tester\app\public`, database `local` on
127.0.0.1:10004. Novamira HQ guide/complete inventory confirmed this profile.
The bundled MariaDB client performed initial read-only access and independent
application-table inventory/checksums before any PHP database or fixture work.

Clean source baseline `894f11cfb5ccc841e7c9f142ceb55ac610df0b12`, master; version
0.1.77/schema8 unchanged. External full-folder source restore point was verified
at this baseline before harness edits. No production PHP, root loader, contracts,
installed plugin state, uploader, credentials, permissions or application tables
were changed. Deferred Windows/native V2.3 research remains deferred.

Explicit CLI `scripts/manual/lifecycle-owned-proof.php` accepts only the confirmed
target flag, a fresh `adblf_<12hex>_` prefix, fixed mode and an owned temporary
plugin-root shape. The parent copied exactly138 runtime source files into that
private root, verified every raw SHA256 before and after testing, and removed the
exact root afterward. It was not installed into the LocalWP plugins directory,
packaged, published or deployed. Existing release rules exclude all manual tools.

`lifecycle-proof-core.php` loads genuine installed core libraries, not wp-config,
wp-load/wp-settings, installed plugins/themes, drop-ins or application shutdown
hooks. Core `activate_plugin`, `deactivate_plugins`, `uninstall_plugin`, wpdb,
dbDelta, options/transients/cron and hook dispatch execute unchanged. Empty genuine
POMO catalogs isolate unrelated locale/authentication discovery; no WordPress
function is replaced by a shim. The core plugin-path registry and constants use
normal initial state. Recovery classes are present, not initialized or activated.
No application request, authentication, dependency download or cron callback runs.

Private connection inherits MYSQL_PWD, never a command-line credential or recorded
artifact. Session statement/lock budgets3 seconds, PHP wall-time limit40 seconds,
memory128MiB. Each mode owns exactly four absent-before-start tables: prefixed
options and the three actual dashboard schema declarations. Core `active_plugins`,
`cron`, persistent options and transient rows therefore exist only in fixture
options. The generated-SQL manifest assertion rejects non-owned table identifiers;
it is not a general-purpose SQL firewall or a privilege boundary for hostile SQL.
HTTP requests are rejected. No database account/grant/user/service changes.

Cleanup arms only after all four collision checks, requires a successful exact
row-count preview, drops only manifest members and verifies absence without
treating metadata read errors as absence. Main and shutdown paths cover ordinary
PHP failure, not power loss/forced termination. Independent bundled-client final
inventory/checksum reconciliation does not rely on the harness cleanup report.

## Targeted functionality matrix and actual results

| Area | Actual evidence | Result |
|---|---|---|
| Core activation/root discovery | Native plugin discovery/requirements, root activation callback, fixture active membership, schema8 and all three tables | Passed |
| Idempotent activation | Second core activation does not rerun callback; exactly one poll and one cleanup event with correct recurrences | Passed |
| Deactivation | Native callback removes maintenance/global lock, preserves five persistent options and exact nonempty site/snapshot/action rows | Passed |
| Reactivation | Genuine core activation succeeds again, restores maintenance without changing those records/options | Passed |
| Default/false uninstall | Core invokes canonical uninstall.php; both modes preserve persistent data and remove only owned maintenance/lock/rate-limit remnants | Passed |
| Explicit purge | In this owned fixture only, true purge constant removes three owned dashboard tables/five persistent options | Passed |
| Unrelated-state sentinels | Unrelated fixture option/transient and cron timestamp retained in every mode | Passed |
| Final cleanup/application preservation | Exact fixture inventory absent, temporary runtime root removed,77/77 existing application-table checksums unchanged | Passed |

Final source-checked run: **264 checks**, three uninstall modes with four lifecycle
groups per mode (12 group executions, not12 distinct features),544 query-filter
observations. Default preserve90/179 queries, explicit false90/179, explicit
purge84/186. Every mode removed all four owned tables (purge itself removes three;
final cleanup removes options). All attempts' namespaces absent afterward.
Success requires actual stored schema, rows and cron effects; an always-failing
or no-op owner cannot satisfy these assertions. No mutation-kill experiment or
new concurrency/transaction/response-loss experiment was performed.

Environment: installed WordPress7.1.3, bundled PHP8.5.12, MariaDB10.6.23.

| Installed core source | SHA256 |
|---|---|
| wp-admin/includes/plugin.php | 8ce517bd2907759934c76663571d5c3ed52ca705c87ecc0063a11fc4c972d02e |
| wp-admin/includes/upgrade.php | 0d1d0e6e9c135452641b5a518ba41f6907e67ac668d27d5e38690f53e846527e |
| wp-includes/class-wpdb.php | e15403e90032dd0508811301505e491d4212de5152581a98f51b744a1a3903bd |
| wp-includes/option.php | fe3f0b7e115b2a50c31162ed6428abe4088c080b0f71b9678d84806168fe90d0 |
| wp-includes/cron.php | dc27910a1ad004eb2aaabc19352246cc0b9e601bf2da870e141646fd9dd95591 |
| wp-includes/plugin.php | d70b9e18d34ab3fe46e15548a5e8e089ea5920832f06a4d89a9d64b2f7938b0d |

## Harness corrections and verification limits

Write-free preflight first exposed the absent initial core plugin-path registry.
After initialization it passed6 checks with zero fixtures. The first fixture
attempt stopped on missing KB_IN_BYTES before completing activation; cleanup
reported success and independent77/77 checksums/inventory matched. Genuine
wp_initial_constants fixed that composition gap. Expanded metadata preflight
then exposed the absent shortcode registry, fixed by loading core shortcodes.php;
it passed7 checks with zero fixtures. No lifecycle method or expected behavior
was weakened. Empty loaded catalogs prevent metadata formatting from requesting
an unrelated real-user locale; this is not locale/authentication qualification.

Initial complete matrix passed255 checks. Review then strengthened metadata
read-error handling and successful cleanup-count witnesses; the final fresh
matrix passed264 checks and final independent reconciliation. An orchestration
comparison initially compared JSON object key order; explicit inventory/checksum
array comparison confirmed equality. No application mismatch or retained table
was found, and no unrelated state was altered to make comparison pass.

New permanent isolated SQL-scope tests2/14 protect valid generated upsert/purge
forms and reject non-owned/mixed targets, TRUNCATE and GRANT. Related schema/
uninstall/scope9/102 passed. These are API/string tests, not the native receipt.
Full localPHP317/1611 with two pre-existing missing-Sodium signing skips;
Node16/16 no failures/skips; PHPCS144, syntax, structure and build passed.
Exact-head publication/CI receipt belongs to the canonical toolkit checklist.
No confirmed production defect or production fix was introduced by this proof.

Source-scoped Light/Security review: manual-only fixed entry, explicit target/
prefix/path/mode gates, exact manifests, successful cleanup witnesses, no raw
credential output, no production inclusion or route; no confirmed introduced
security defect. No bloat/UI workflow or automated-suite restructuring needed.
SQL assertion tests are proportionate harness protection, not a whole07A review.

This closes the **bounded core-backed lifecycle/data/cron preservation** gate,
not full installed-site/browser/admin capability/nonce or filesystem plugin-
deletion acceptance. Query Monitor/WP Debug Toolkit/application debug.log were
not bootstrapped by this private CLI composition; native stderr and SQL outcomes
are the evidence, not an assertion that application diagnostics are clean.
No UI/HTTP test, real user, multisite, minimum-WordPress physical matrix, concurrent
traffic, atomic DDL, backup/restore, final08/13 or FULL readiness claim. Existing
application plugin state and production wiring remain untouched; release rows
remain pending and require their owning workflows.

Recommended next action: resume canonical **05 Database Review**, repository-only
inventory first. Any newly identified schema/data/lifecycle mutation still needs
its own approval. Product progress remains2/8 plus the separately approved schema
and lifecycle proofs; the proof itself is not counted as a safe-local task.
