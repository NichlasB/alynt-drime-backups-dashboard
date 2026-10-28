# Fixed Native Construction / Adapter Qualification Audit — Blocked Before Fixtures

## Authority and fresh baseline — 2026-10-28

Approved R3 Plugin Tester local-only Fixed Non-Reconnecting Connection
Construction and Native Adapter Owned Qualification/Cleanup Proof, audit first,
through validation/commit/push/exact-head CI. Fresh exact confirmation:
`plugin-tester`, `local-only`, LocalWP “Plugin Tester”,
`http://plugin-tester.local`, WordPress at
`C:\Users\Captain\Local Sites\plugin-tester\app\public`, resolved database10004.
Clean/synced restore point: `3d0a8f0d2a7052d3ef3643d1e458de69065a956e`.
Novamira HQ guide/complete inventory read; exact local URL present. No WordPress
ability or application bootstrap ran. Maximum autonomous R2 is not raised.

The initial sandboxed listener check was misleading. Elevated OS/TCP checks
confirmed database10004/web10005 listening; no service restart was needed or
performed. Initial SQL used the current LocalWP-bundled MariaDB10.6.23 mysql.exe
(`bin/win32/bin/mysql.exe`), host127.0.0.1/database local, registry credential
source via temporary MYSQL_PWD, restored afterward. No credential was printed
or persisted. No PHP MySQL/WordPress bootstrap was used for initial access.

## Read-only native-profile audit and preservation

Actual server10.6.23-MariaDB; database local; autocommit1/in_transaction0.
wp_options is InnoDB/utf8mb4_unicode_520_ci, four expected columns, primary
option_id, full unique option_name and nonunique autoload indexes. No existing
adbp_/adbn_ proof tables. Metadata/checksum reads only, no option/user payloads.

Captured the ordered77-table manifest and checksums before the library audit,
then independently reconciled afterward: all77 identical, no extra/missing
table, zero proof tables and no retained transaction. No fixture, DDL, DML,
native candidate connection, lock/failure experiment, user, cron, lifecycle,
cache backend, service, application or live state change occurred.

## Exact blocker: initial construction is intentionally absent

The prior [genuine-core context proof](SOURCE_POLICY_CORE_CONTEXT_ZERO_SQL_PROOF.md)
establishes the installed7.1.3 callback/marker/never-opened cleanup layer. Its
215/12 result remains historical here, not a native proof or a rerun. It does
not establish a connection provider.

Actual unchanged source:

- [No_Reconnect_Connection](../includes/class-source-policy-no-reconnect-connection.php)
  has a protected constructor which only sets ready=false. create() returns
  unavailable. Final db_connect() and check_connection() always return false;
  there is no first-connect exception, bounded socket construction, trusted
  setup source or native ownership-transfer implementation.
- Its admit_profile requires native_state=active, ready=true, a bound valid
  context and a complete qualification descriptor. Final query/execute refuse
  before that admission. No implemented path establishes those native facts.
- Genuine installed wpdb::__construct calls the receiver's virtual db_connect;
  invoking it on the fixed receiver therefore still refuses. The inherited
  db_connect body initializes mysqli, connects, initializes/sets charset and
  calls set_sql_mode/select. Calling that body through an artificial bypass
  would not qualify bounded connection startup or the pre-qualification query
  boundary. It is not a permissible native-proof shortcut.
- The [adapter](../includes/class-source-policy-native-adapter.php) is also
  construction-closed. The RAM tests privately inject simulated active handles
  and descriptor DATA, explicitly not a real provider. Copying that injection
  with a separately opened/transferred native handle and guessed facts would
  not prove this missing fixed construction/lifecycle contract.

The [approved design](SOURCE_POLICY_NATIVE_ADAPTER_INTEGRATION_DESIGN.md) requires
bounded, error-safe initial construction, trusted connection/settings identity,
genuine core charset processing and independently established session/schema/
cache/hook conditions. It forbids copied core executors, private core-method
bypasses, global-session mutation, arbitrary facts and unbounded fallback. The
[foundation](SOURCE_POLICY_NON_RECONNECTING_INTEGRATION_FOUNDATION.md) explicitly
records these construction/profile prerequisites as unimplemented. The previous
recommendation to run the complete native proof jumped ahead of this prerequisite.

The repository/manual harness also provides no pinned6.0 source/profile or
genuine minimum-core execution path. This is not a claim that no source exists
elsewhere on the machine. Minimum-core compatibility cannot be inferred from
RAM descriptor branches or the installed7.1.3 library proof; no source download,
installation, compatibility change or minimum-version increase was attempted.

## Ten-check handle-free diagnostic, not a successful native proof

Bundled PHP8.5.12 `-n` (mysqli/pdo_mysql absent), fixed core path/hash and
genuine load/plugin/WP_Error/wpdb libraries; no wp-load/wp-config/application.
Reflection allocates the exact fixed class and invokes its own protected
constructor. Then the genuine parent constructor is invoked on that same
receiver with inert, non-secret/non-application arguments. Virtual dispatch
still reaches the unchanged final refusal; no native driver is available or
called. No subclass, overridden executor, active-state/descriptor injection or
handle transfer is used.

Actual diagnostic:10 checks, native_connections0, sql_queries0, fixtures0;
outcome initial-construction-and-prequalification-bootstrap-not-implemented.
Checks cover absent drivers, pinned wpdb source, protected/never-opened own
constructor, both unavailable factories, final db_connect refusal, parent
constructor remaining handle-free, unavailable admission/query, execute
refusal with zero queries, and truthful repeated never-opened cleanup with no
query callbacks. The core wpdb SHA256 remains
`E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD`.

This is rejection-based diagnosis of an absent prerequisite, not positive
qualification, native success, physical-attempt counting, `%s` escaping proof,
semantic mutation-control evidence or task completion credit. The already
recorded positive context and RAM suites retain only their actual scope.

## Validation / publication scope

Only this audit record and owning plan/review pointers change. No PHP source,
tests, manual harness, runtime root/factory/form/handler, asset/catalog,
schema8/version0.1.77, dependency, CI or packaging change. Uploader/toolkit stay
read-only. Historical nine-check/215-check evidence is not refreshed; no whole
07/07A/13/FULL or toolkit readiness row is marked complete. No UI/UX workflow
applies to this documentation-only checkpoint. Source-scoped contract review
identified the missing construction stage; this is not a new live vulnerability
or a regression in the proved library lifecycle.

Actual local validation of the unchanged code: PHP1263 tests/5702 assertions,
no failures, only the two existing missing-Sodium signing skips;
PHPCS187 configured files and structure143 runtime/210 support/179 test files,
177 class names pass. Diff hygiene and scoped relative links pass; all
runtime/test/manual-script/asset/dependency/CI paths are byte-unchanged from
the restore point. No new native or automated-test success is claimed.

## Implementation-plan progress and exact next gate

Native qualification stopped before fixtures; no safe-local/native task-count
credit. The safe lane has reached a construction/qualification contract
decision, not another generic cleanup opportunity. Production lost-update
repair, native adapter acceptance and final integration remain open.

Recommended next gated slice: **repository-only Source-Policy Bounded Initial
Native Construction and Pre-Qualification Bootstrap Design**, design-only.
Specify one private first-connect/setup lifecycle (separate from forbidden
reconnect), bounded failure/partial-cleanup ownership, trusted source/settings
and minimum-core/current-profile qualification, and setup-versus-admitted
dispatch ordering without fabricated active grants. Keep native execution,
storage writes, all public factories, authentication, production wiring and
release/deployment disabled during that design. Then implement the isolated
construction foundation before resuming this approved owned native proof with
fresh target/readiness/preservation. This proof approval does not authorize
relaxing runtime guards or inventing the missing provider contract.
