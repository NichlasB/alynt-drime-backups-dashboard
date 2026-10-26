# Schema Upgrade Failure Detection and Retry Safety Foundation — 2026-10-26

## Approved repository-only scope

User explicitly approved the R3 storage-lifecycle slice after the FULL review's
High failure-handling finding. Baseline dashboard
`0dc165e5e3e1aca9d4654e073e41909fb17aac68`, master, clean; existing restore archive
and baseline checked before edits. Source0.1.77/schema8/minimum WP6.0/PHP7.4 stay
unchanged. No new dependency, public route, account, permission or option name.
Only the source/test foundation is authorized; no WordPress, SQL, LocalWP, actual
option/cron/activation, client/Drime, native experiment, installer, package,
release/tag/deploy, backup or restore operation ran.

## Plan and implementation

1. Preserve all three existing CREATE statements, table names and definitions.
   Install each through dbDelta, then independently require exact table presence
   through prepared/escaped SHOW TABLES LIKE and no reported query error.
2. Re-run each declaration through `dbDelta($query, false)`: require an empty
   comparison result and no reported metadata error before completion. A list of
   planned changes from the executing call is not proof of success. See the
   [core API/source](https://developer.wordpress.org/reference/functions/dbdelta/).
3. Record version8 only after all three checks; verify its readback rather than
   trusting update_option's ambiguous false return for unchanged values. A forced
   reinstall first invalidates a previously current marker; failed invalidation
   stops before DDL. Failed normal upgrades preserve the older marker for retry.
4. Return true/WP_Error, never raw SQL/connection details. Treat corrupt compound
   version options as not-current. Preserve the existing current-version fast path.
5. Activation stops before scheduling on storage failure. Normal initialization
   registers only a manage_options-protected persistent error notice on failure,
   not dashboard/REST/polling hooks. Verified positive callers still initialize.

The comparison uses WordPress's schema semantics, not a parallel DDL parser or
an assertion of full structural/atomic equivalence. Core ignores some differences
(including some index subparts); no extra constraints or corrective migrations
were invented here. Already-current markers are still trusted on normal startup:
this is not an audit/automatic repair of historical false-current installations.
Previously current forced reinstallation is verified afresh. All extra metadata
work is confined to install/upgrade attempts, not every healthy request.

## Evidence and non-vacuous tests

Actual production Storage/Plugin/Activator classes run in bounded, freshly owned
PHP CLI processes using fixed in-memory API doubles. The fixture ABSPATH contains
an empty include target, explicitly not installed WordPress. No dbDelta/core SQL
executor, socket, connection or persistent storage is loaded. Runner reaps its
owned child on every outcome and has a10-second deadline.

Baseline regression reproduced premature completion after a failed table update.
Positive/negative cases cover each table, pending comparison on all three tables,
malformed comparison, metadata errors/throws, safe Throwable handling, marker
write/invalidation failure, partial-failure retry, forced/current/numeric/corrupt
markers, completion ordering/autoload, both failed callers and successful callers.
Golden SHA256 assertions pin the three original emitted DDL strings byte-for-byte.
No always-unavailable owner or fake success path substitutes for positive cases.

Five temporary semantic controls (table presence, convergence, marker readback,
bootstrap admission and activation admission) each failed through assertions, not
fatal errors; original source restored. Final whitespace check caught two
indentation changes from control restoration; corrected before final validation.
The three original source SHA256s then matched the pre-control values. A later
intentional notice role/comment is separately tested, not a mutation residue.

## Focused reviews and limits

Feature scope: schema-install completion, retry and caller admission.
Files reviewed: 12 changed source/test/record files plus direct bootstrap callers.
Risk areas touched: DB lifecycle source, admin UI, Cron admission, owned test processes.
Issues found: 0 additional confirmed defects after the approved repair.
Issues auto-fixed: 0 additional defects; persistent notice gained an explicit alert role.
Issues requiring manual decision: 1 core-backed qualification boundary below.

Canonical Feature Light → UI/UX → final focused Security, source-scoped. Cohesive
three-file lifecycle fix and existing layout; no split/Bloat workflow needed.
Notice uses native error styling, translated/escaped text, role alert and existing
admin capability; persistent configuration failure is intentionally non-dismissible.
No dynamic widgets, submitted forms or JS changes. SQL metadata values prepared
and LIKE-escaped, no raw request or user-controlled path, no diagnostic internals
logged. Fixed test-worker path/owned process only. No additional confirmed
security defect/new07A handoff; real core/database risk remains an explicit gate.

Feature passes source-scoped light and focused security review; no additional
confirmed security defect requires new regression coverage. This does not close
the FULL review's database/lifecycle qualification requirement.

Final local validation: focused6 tests/84 assertions; full312/1583 with exactly2
pre-existing Sodium signing skips on Windows PHP8.5.1; Node16/16, fail0/skip0.
Build, PHPCS138 files, structure, Composer strict validation, Composer/npm audits
(no advisories), and diff hygiene passed. Global `composer` was unavailable;
the existing repository `composer.phar` completed both checks instead. Exact-head
CI and publication evidence is recorded in the toolkit checklist after push.

This is not genuine WordPress authentication, physical database/migration,
concurrency/transaction, DDL rollback, actual schedule/activation/uninstall, or
whole-plugin release readiness. No claim that a live installation was corrupt,
fixed, inspected or qualified. FULL03 remains incomplete until core-backed
failure/retry qualification; later dependent reviews remain pending.

Recommended next gated slice: **Plugin Tester local-only Schema Upgrade
Failure/Retry and Core-Backed Owned Cleanup Proof**. Requires separate approval
and exact target/owned disposable database fixture confirmation. Preserve existing
application tables/options/users/schedules and production wiring. Do not run a
normal application migration, activation or purge on that existing state.
