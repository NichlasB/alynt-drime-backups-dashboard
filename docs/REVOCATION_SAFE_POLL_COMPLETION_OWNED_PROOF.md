# Revocation-Safe Poll Completion Owned Database Proof — 2026-10-26

## Authority and exact scope

User approved this R3 proof and confirmed the resolved target: `plugin-tester`,
`local-only`, LocalWP “Plugin Tester”, `http://plugin-tester.local`, WordPress at
`C:\Users\Captain\Local Sites\plugin-tester\app\public`, database `local` on
127.0.0.1:10004. Novamira HQ guide/complete inventory confirmed the exact profile.
No WordPress Ability was executed: direct bundled-client checks and isolated
core-library connections are more appropriate than bootstrapping an application
request for this private SQL/locking proof. No live target is configured here.

Clean/synced source baseline `d614fe33a1282837b6fe43852cf78095426db8cb`, master.
The [foundation](REVOCATION_SAFE_POLL_COMPLETION_FOUNDATION.md) is unchanged.
No production PHP, schema8 declaration, version0.1.77, loader, installed plugin
state, application option/user/cron, credential, permission or wiring change.
Uploader/toolkit are unchanged. No client/Drime/HTTP, actual polling, lifecycle,
schedule, migration, release, package, tag, deploy or service operation ran.

External source-only restore point:
`C:\Users\Captain\Documents\AI Workflows\Toolkits\toolkit-snapshots\alynt-drime-backups-dashboard\alynt-drime-backups-dashboard-20261026-215453.zip`,
matching `.txt` sidecar; SHA256
`07B7F032278C1442BE3C4C0876AF1D036E7A79D61AA7552025CB3BED47553A07`.
This is not a site backup/package, and no source restore ran.

## Genuine core/storage and containment

The bundled MariaDB10.6.23 client performed initial read-only identity/inventory
and physical EXTENDED checksums for all77 existing tables before PHP connected
or any fixture was created. Installed WordPress7.1.3, bundled PHP8.5.12 with
mysqli/mbstring, genuine core wpdb/formatting/error/cache/hook libraries and the
actual repository success/failure/revocation methods execute unchanged. No
WordPress API shim or alternate write implementation supplies the outcome.

`scripts/manual/poll-completion-owned-proof.php` is CLI-only, requires the fixed
confirmed-target flag, `adbrp_<12hex>_` prefix and closed role set. The flag is not
human authorization; Site Operations approval/confirmation remains mandatory.
It loads core libraries directly, not wp-config/wp-load/wp-settings, installed
plugins/themes, drop-ins, application authentication or shutdown hooks. Genuine
in-memory core cache supplies only a fixed UTF-8 charset; empty genuine POMO
catalogs prevent unrelated locale discovery. No persistent options fixture is
needed. Synthetic credential ciphertext is never decrypted or used to authenticate.

Each attempt owns one absent-before-start sites table, created with a schema-only
`CREATE TABLE ... LIKE wp_alynt_drime_dashboard_sites`; no application rows are
copied. Its26 columns, InnoDB engine and case-insensitive collation are verified.
Actual prepared writes preserve quotes, backslashes and UTF-8 fixture bytes.
Only schema metadata of the existing sites table is used by that fixed clone
statement. All test DML, transactions and DROP target the owned table exclusively.

The generated-SQL manifest assertion allows fixed owned operations and narrowly
filtered read-only transaction/process metadata, not arbitrary SQL. It rejects
application/mixed targets, multi-target DROP/UPDATE, grants, global settings,
TRUNCATE and KILL. It is a first-party assertion, not a general SQL firewall or
database privilege boundary. Three permanent socket-free guard tests protect
this limited contract, not server execution or hostile SQL parsing.

Private connections inherit MYSQL_PWD without recording it. Statement budget3s,
lock budgets2s, wait-witness deadline1.5s, whole-proof budget40s/memory128MiB.
Owned child processes receive only fixed role/prefix/temp-path arguments. Exact
temporary marker/log manifests are bounded and removed; marker reads use the
writer's lock. Only proc_open-owned resources are joined/terminated, never an
application process or database session. No account/grant/listener is created.

## Final physical evidence

Final source-checked namespace: `adbrp_9b6d34a2c108_`.
Write-free preflight passed5 checks/one metadata query/zero fixtures. The final
physical matrix passed **269 checks across eight groups**,261 parent query-filter
observations plus22 worker observations,3.796s. Query counts include metadata,
fixture setup/teardown and observation, not just completion statements.

| Group | Required physical behavior | Result |
|---|---|---|
| Success/first-poll | Actual writes and awaiting-first-poll promotion; legitimate zero-affected-row verification; bounded one/two-query completion path | Passed |
| Failure | Actual retry/error writes; unchanged rows; missing/pending/revoked rows; write/read server failures and recovery | Passed |
| Identity/credentials | Real case-insensitive comparison control succeeds while byte-sensitive completion rejects case-only changes in all three fenced fields | Passed in success/failure groups |
| Readback interleavings | Actual revocation or change to an intended persisted field after zero-row UPDATE but before SELECT rejects stale completion | Passed in success/failure groups |
| Nullable context/ID | Null and empty credential failures can be recorded, success cannot; other physical row is not the target | Passed |
| Two revocation-first orders | Captured success/failure worker actually waits behind uncommitted real revocation; rejects after commit | Passed |
| Two completion-first orders | Real success/failure holds the row; revocation worker waits, then clears enrollment/credentials after commit | Passed |
| Held-lock failure | Actual blocking relationship, unchanged lock budget, actual server lock-wait-timeout classification, false storage result, then committed revocation | Passed |

The eight group labels combine these cases; table rows are not extra features.
Each of five ordered cases requires an actual INNODB_LOCK_WAITS/INNODB_TRX
requester/blocker relationship bound to the two owned connection IDs, not elapsed
sleep alone. The readback-gap callbacks are deterministic test-only rendezvous
with genuine SQL, not additional simultaneous-worker proofs. Four deliberate
server syntax/unknown-column faults and one real lock-wait timeout are checked
through false/last_error. Core's private-connection error-print suppression avoids
raw SQL logs; it does not replace those assertions or mask a production failure.

Cleanup requires successful metadata and bounded exact row-count previews, rolls
back the owning transaction, drops only the manifest table and verifies absence.
All owned workers/marker directories are reaped. Independent bundled-client
reconciliation after attempts and final success found exactly the original77
tables and **77/77 unchanged physical checksums**; no `adbrp_` tables remained.
Independent process/temp-directory inspection found no retained proof workers
or owned directories. This covers normal failure/termination, not power loss or
hostile interference with the local process/filesystem.

## Harness corrections and proof limits

- Initial genuine sanitization requested private-prefix options; the manifest
  rejected it before execution. Cleanup and77 checksums matched. Genuine core
  cache now provides the fixed charset, and preflight exercises sanitization.
- Strengthened preflight exposed missing installed `utf8.php`; including that
  genuine library fixed composition without shimming a function or adding storage.
- Early20ms metadata sampling repeatedly reused an old transaction snapshot;
  diagnostics showed a live updating worker absent from that snapshot. The
  [version-matched MariaDB source](https://github.com/MariaDB/server/blob/mariadb-10.6.23/storage/innobase/trx/trx0i_s.cc#L900-L915)
  requires over100ms idle between cache reads. Sampling is now200ms while the
  1.5s witness deadline,2s lock limit and real-witness assertion remain unchanged.
- Review added readback-gap cases. One initial test changed plugin_version even
  for failure completion, which does not write that field. It now changes
  overall_status, an intended field in both paths; the stale assertion is retained.
  Final runs added explicit server timeout classification and single-target guards.

No production defect or source repair was introduced by these harness corrections.
The earlier216/8 and264/8 successes are intermediate evidence;269/8 above is the
final harness receipt. No artificial success, removed assertion, relaxed native
budget, schema migration or application-state repair made it pass.

This qualifies the existing High finding's repository write fence on the recorded
WordPress/PHP/MariaDB profile. It is not browser/authentication/installed-plugin
acceptance, minimum WordPress/MySQL or multisite qualification, a crash/response-
loss experiment, an atomic snapshot-plus-site-update transaction, monotonic
generation/ABA protection, or a guarantee of zero events/snapshots after revocation.
Those limitations in the foundation remain unchanged. Deferred V2.3 stays parked.

## Source-scoped reviews, QA and resume

Explicit review baseline: `d614fe33a1282837b6fe43852cf78095426db8cb`; five new
manual helpers/entry, one test class and four owning plan/review records only.
Canonical Feature Light and final focused Security: fixed CLI/role/prefix/path
gates, closed identifiers/prepared values, exact cleanup, no real secret output,
no new public endpoint, no runtime or package wiring. Five harness findings above
were fixed; no unresolved defect in the approved proof scope. Meaningful helper
boundaries follow core composition, SQL manifest, process ownership and case
matrix responsibilities. No additional Bloat/split or backend UI/UX workflow
is needed. These are source-scoped outcomes, not refreshed whole-plugin rows.

Local related64 tests/346 assertions, no skips; fullPHP366/1892 with exactly the
two existing missing-Sodium signing skips, no failures/errors/warnings. New
SQL-scope tests3/25. Node16/16 fail0/skip0, PHPCS149, structure, syntax, asset
build without tracked output change, strict Composer validate and Composer/npm
audits (zero advisories) passed. Native proof is manual-only; CI never invokes it.
Exact-head CI/publication receipt is recorded at closeout, not inferred from the
predecessor run. Runtime method SHA256 is unchanged:
`77C4C6514FE8B46BCDC498683586B32A6758CB7EC79594142E7A7E2932D16308`.
Installed wpdb SHA256:
`E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD`;
formatting `350F6B32303915DF970BB0E3EA360FCD54BB0B8A7ECC3D504C3D860AB9468D32`;
UTF-8 `6115A3FFAC15321E08FCB3EBE39BA0BD83121E0FECC226911F4A801FD896EC77`.

The High conditional-write qualification gate is closed. Canonical05/FULL remain
incomplete because the separate Medium scheduled-retention error finding is open.
Toolkit historical rows are untouched under this proof approval. Safe-local
product count remains3/8; this separately approved native proof is not task4.
Recommended next gated slice: **repository-only Scheduled Retention Failure
Reporting Safety Foundation**: surface both cleanup repository errors through
existing safe observability and isolated tests, without running cleanup, changing
retention/schedules/schema, touching a site, packaging or deploying.
