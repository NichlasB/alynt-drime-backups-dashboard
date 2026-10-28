# Bounded Initial Native Construction and Pre-Qualification Bootstrap Design

Later approved repository-only [isolated foundation](SOURCE_POLICY_NATIVE_BOOTSTRAP_FOUNDATION.md),
2026-10-28, implements this private orchestration using extension-free synthetic
API/IO tests. This design's original evidence remains historical, not native
qualification; actual source/provider/matrix and production wiring stay gated.

## Authority and immutable baseline — 2026-10-28

Approved repository-only design, through validation/commit/push/exact-head CI.
Clean/synced restore point: `cd948727e67eb4ff1be4db8fb22c67ef92438a1a`.
R1 documentation for an explicitly gated future R3 connection/SQL boundary;
maximum autonomous R2 is not raised. Version0.1.77/schema8 and declared
PHP7.4/WordPress6.0 remain unchanged. Only this design and owning plan/review/
audit references change. No PHP/test/harness/loader/asset/dependency/CI change.

No LocalWP/site inspection, authentication, database/SQL/storage/cache/user/cron
operation, native connection or experiment, fixture, configuration/credential/
permission/service change, client/Drime action, release/package/tag/deploy.
Public official documentation/source was read online, not installed-site files.
Uploader/toolkit remain read-only; no task-count or whole-release credit.

Inputs: [construction audit](SOURCE_POLICY_NATIVE_CONSTRUCTION_AUDIT.md),
[native integration contract](SOURCE_POLICY_NATIVE_ADAPTER_INTEGRATION_DESIGN.md),
[owned callback design](SOURCE_POLICY_CORE_PLACEHOLDER_LIFECYCLE_DESIGN.md),
[dormant foundation](SOURCE_POLICY_CORE_PLACEHOLDER_LIFECYCLE_FOUNDATION.md).
The prior ten-check/77-table audit and215-check library proof remain historical,
not rerun or promoted to native construction evidence by this document.

## Decision: one private first-connect path, no reconnect exception

Keep final db_connect() and check_connection() unconditional refusals. Do not
call parent db_connect, alter reconnect_retries as a substitute,
invoke private core executors, or copy wpdb::query. Introduce a separately named,
private, single-use initialization path on the exact fixed owned receiver.
It alone allocates/connects its own mysqli object and establishes qualification
before the existing ordinary checked-prepare/query/execute paths can operate.

This deliberately supplies the missing startup contract, not a temporary
relaxation of admission. Public query/execute do not gain a setup mode or SQL
allowlist. All public factories remain unavailable through the next isolated
foundation. No public bootstrap method, caller connection/SQL/callback/descriptor
argument, environment activation switch, global pool, persisted ledger or
administrator bypass is introduced. There is no imported or transferred handle.

The private initialization can be invoked by explicit isolated/native proof
composition at its own authorized layer. A later native harness must invoke
that actual path on the exact class; it must not inject dbh/ready/active/facts
to simulate construction. Final production composition remains separately
gated. No Reflection, test source pin, or manual proof CLI flag is a production
provider. No partial receiver/handle leaves private composition. Inherited core
preparation is a library operation, not native dispatch permission; arbitrary
trusted PHP execution/Reflection is not sandboxed by these boundaries.

### Parent object initialization is not the parent connector

The [archived WordPress6.0 source](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-includes/wp-db.php)
allocates its driver inside db_connect; charset and SQL-mode helpers contain
direct driver operations, and set_sql_mode invokes incompatible_sql_modes.
The recorded installed7.1.3 constructor dispatches virtually to db_connect.
These facts explain the startup gap; they do not qualify either native profile.
The selected initial connector uses documented mysqli primitives, then verified
genuine core charset/mode helpers, not a second general SQL executor.

Minimum-core detail:6.0 initializes its private mysqli-selection flag in its
constructor, rather than defaulting to mysqli. Therefore select one verified
**object-only parent constructor invocation**, under the unchanged final
db_connect refusal, before allocation. Its virtual connect call must return
false with zero driver allocation/IO. This is not invoking the parent connector.
Reject legacy-driver overrides or an unexpected constructor closure first;
require actual initialized mysqli selection afterward. Do not set core-private
flags with Reflection, copy constructor bodies, or rely on a modern default.
The recorded ten-check audit illustrates parent dispatch remaining disconnected,
but does not qualify this driver-selection behavior natively.

## Trusted setup frame and eligibility before allocation

Before even allocating a driver object, the future private composition must:

1. Preserve capability -> action nonce -> uncoerced intent/site/record guards
   before this owner is reached. This design does not wire or implement those
   actual request calls. Ordinary classifier/read paths do not initialize it.
2. Establish a code-owned source/profile binding independently of descriptor
   DATA: exact concrete receiver, audited declaration files/method closure and
   trusted core context. Version text or a caller's matching hash is insufficient.
3. Snapshot database, table/prefix, endpoint, credential source, charset/collation,
   flags and single-site/core-cache/drop-in posture from trusted core composition,
   not request fields, arbitrary objects, cached option payloads or a new settings
   screen. No wp-config include or credential discovery occurs inside bootstrap.
   Installation/configuration-setup mode and legacy-driver overrides are not
   eligible operating profiles; they are rejected before core initialization.
4. Reject an ambient/owned alias; bind the optional stock ambient receiver by
   identity without adopting callbacks or borrowing its handle. No global
   session, cache backend or live connection setting is changed.
5. Independently qualify existing option/sanitation/cache callback domains by
   the fixed source/profile policy. Do not stamp a current unknown graph as
   trusted merely because a new fingerprint matches; unknown callbacks refuse
   before allocation. Known positive observers need their own exact evidence.
6. Validate finite types/lengths and the complete selected transport profile.
   Keep secrets process-local and out of results, descriptors, logs and traces.
   No persistence, printable configuration dump or connection fallback.

First native qualification target: nonpersistent mysqli/mysqlnd, explicit
numeric loopback TCP address/port, no DNS/default endpoint, socket/pipe,
compression, custom client flags, init command, multi-statements or local infile.
Use explicit database selection in the one connect call; do not invoke core
select() and its bail/output path to recover a failed selection. Custom TLS,
remote endpoints, routers/replicas, persistent handles and custom connection
profiles are unavailable until separately qualified, never downgraded to this
profile. This local target is not presumed equivalent to production hosting.
Missing production/minimum-profile acceptance blocks final wiring, not an
unannounced removal of the existing supported save feature.

Fixture composition may bind an exactly confirmed disposable domain in its
private proof process; it cannot enable a fixture prefix/backend in production.
The later initializer derives and verifies facts on that domain itself.

## Monotonic ownership and admission

The names below specify required states, not an already implemented API:

| Phase | Owned driver/context | Ordinary adapter dispatch |
|---|---|---|
| fresh/preflight | No handle; source/setup frame, one-time object-only core initialization and callback eligibility checked | Refused |
| allocated | First-attempt latch consumed; exact new driver immediately owned; options checked | Refused |
| connected-unqualified | One connect acknowledged; ready=false; callback seal held | Refused |
| setup/qualifying | Only fixed private steps on that handle; bounded readbacks/metadata | Refused |
| active | Complete internal evidence and unchanged source/identity/seal; ready=true published last | Existing full admission required |
| closing/closed | Dispatch blocked first; driver and callback cleanup independent | Refused |
| rejected/closure-unknown | Terminal; no new attempt, adoption or fabricated never-opened state | Refused |

Consume object-initialization/allocation/connect latches before their respective
calls. Failure,
reentry, a closed instance, changed context or a later connection loss never
resets them. A new public command is not permission to resume a partial owner.
Track driver allocation separately from successful native connection and from
callback leasing. False/exception/unknown acknowledgement cannot set active.
No callback is allowed between the last qualification check and publication
of the private descriptor/ready/state tuple. No DATA factory activates a handle.

An allocated-but-unconnected object is not the never-allocated state. A failed
real_connect may still need driver cleanup. A lost connected handle never
becomes never-opened because dbh is null or ready is false. The current close
implementation must be extended deliberately to these partial states in the
next foundation, preserving its global-alias and once-only guarantees.

## Bounded one-shot connection and setup sequence

This is the selected future sequence, not permission to run it now:

1. Validate the source/setup frame and setup-hook domain. Invoke the genuine
   parent constructor once solely to initialize core object/driver-selection
   state; its final virtual db_connect refusal remains mandatory. Verify
   mysqli selection and ready=false/no handle/no query afterward. Bind the
   constructor, its called methods and relevant instance fields per core profile;
   unexpected initialization/effect refuses before allocating. Establish the owned
   core placeholder registration and seal using the existing exact owned/optional
   ambient context. Registration ownership is captured before warm-up. No native
   operation follows failed admission; never reseal a changed hook graph.
2. Allocate exactly one mysqli object internally and record ownership before
   any subsequent fallible action. No constructor which implicitly connects.
   Require accepted connect/read options before the sole real_connect call.
   Proposed starting values: connect2s/read3s; local infile disabled. Unsupported
   option/driver/profile is unavailable, not a default-timeout fallback.
3. Connect once to the explicit frozen domain/database. Read genuine server/
   client/selected-database/session identity; do not derive them from requested
   settings alone. Only genuinely acknowledged driver lifecycle may initialize
   the remaining core connection-state fields required by the pinned helpers;
   no copied private state or guessed compatibility flags. Keep ready=false and
   no qualification descriptor throughout.
4. Apply only this owned session's fixed MariaDB limits: statement3s, InnoDB
   lock1s, metadata lock1s; verify actual values. No GLOBAL SET, DDL, schema repair,
   transaction, retry, reconnect, credential or service action. Unexpected
   autocommit/transaction state refuses rather than being repaired.
5. Invoke the audited genuine init_charset/determine_charset/set_charset and
   SQL-mode helpers through fixed private orchestration, with source/virtual
   dependencies checked. Preserve core preparation/escaping, never copy it,
   strip placeholder markers manually, or install an identity query filter.
   Only verified charset/collation inputs and bounded canonical SQL-mode tokens
   reach these helpers. Select the database in step3, not via a bail-capable
   fallback. Reconcile actual negotiated charset/collation/mode afterward;
   helper void returns or silence are not acknowledgements of correct setup.
   Check fixed native error status immediately after each helper, before a
   later statement can clear it. Known errors refuse even if a subsequent
   readback could coincidentally match; expected void/no-op behavior requires
   the audited branch and verified end state, not an invented success receipt.
6. Perform fixed bounded session and schema/index metadata reads on that exact
   handle. Verify the expected four-column options shape/full unique identity,
   InnoDB, selected database/table, autocommit/no transaction and limits. No
   option row, application payload or write is read/attempted to qualify setup.
7. Recheck immutable source/frame, handle/ambient identity and hook seal. Build
   the existing descriptor internally from those observations, not caller DATA;
   publish active/ready only after every condition is satisfied. Disable all
   bootstrap entry/step capability permanently. Ordinary owner operations then
   retain their original read/CAS/ambiguity/cache/hook/uncertainty budgets.

[mysqli options](https://www.php.net/manual/en/mysqli.options.php) are configured
between initialization and connection; the read option is available within the
declared PHP floor. [real_connect](https://www.php.net/manual/en/mysqli.real-connect.php)
accepts explicit endpoint/database/flags. These API facts are not evidence of
working bounds or compatibility on this plugin's native matrix.

### No public pre-qualification SQL channel

The private bootstrap uses fixed step identities, never execute(sql), a generic
probe callback, prepared-SQL input or a public flag that bypasses admit_profile.
Step text/templates are code-owned; identifiers are frozen/strictly validated,
values use genuine preparation where needed. Genuine helpers can issue direct
native setup calls; those effects belong to their audited finite source closure,
not to the application's query-filter instrumentation. Source changes or an
unexpected dependency/effect refuse before use; no copied core query engine.

Account for all helper-native operations and free all metadata results in finally.
Proposed envelope: at most16 fixed driver-command effects, including worst-case
helper work, metadata results capped at5 columns/4 index rows (one extra detects
unsupported shape), scalar names/types bounded before use, mode text<=1024 bytes
and<=32 canonical tokens. Native capability bits/protocol calls are counted
separately, not mislabeled SQL queries. A source profile exceeding this finite
catalog is rejected; it does not enlarge budgets dynamically.

Proposed elapsed bootstrap budget20s, checked before/after each fixed step.
Connect/read timeouts and a monotonic clock do not prove a hard process deadline
or interrupt an already-blocked synchronous call. No such guarantee is claimed.
Later native qualification must establish ordinary/failure elapsed behavior for
each admitted local profile. An unmet required bound blocks that profile; no
automatic worker/relay/OS toolchain or unbounded fallback is introduced here.

## Setup sources, hooks and global-state discipline

Extend source binding to every actually reached setup method/function and its
virtual dependencies, including the object-only constructor, driver-selection/
compatibility accessors and fields, parse_db_host if used, init/determine/set charset,
has_cap/db_version/db_server_info, SQL-mode processing, core preparation and
fixed final dispatch/close. Verify expected receiver, declaring class and
source file; changed subclasses/traits/magic/autoload probes are not admitted.
Do not assume the historical installed profile has the same complete closure
as archived6.0: its setup bodies must be separately pinned and audited later.

Before invoking any setup helper, explicitly reject nonempty `all` and
`incompatible_sql_modes` registries for this first profile. Snapshot their
absence with the other relevant hook metadata before warm-up and compare it
through setup and activation; the current fingerprint does not cover these
two domains. Unknown callbacks are never run to discover eligibility. This
requires a deliberate extension in the next isolated foundation, not a claim
that the unchanged current provider already enforces it. No remove_all_filters,
ambient preparation/cleanup, temporary filter disabling or snapshot refresh.

Respect the existing mysqli reporting mode; do not switch it process-wide to
make a constructor succeed. Handle both false/warning and exception behavior
locally, restoring any scoped PHP warning handler in finally. Raw driver text,
SQL, credentials, marker tokens and traces never reach public errors/audits.
No helper bail/die/translated error-page path is part of the admissible closure.
Request-visible bootstrap failure is the fixed unavailable result, not saved,
conflict, an authoritative revision or an assertion that no session SET occurred.

## Partial cleanup and operation uncertainty

On every terminal bootstrap path, block dispatch first and attempt only its
exact owned native cleanup once; independently finish the exact callback lease.
Do not close global wpdb or another handle, borrow a new connection to classify
failure, restore a shared registry, undo settings on an ambient session, or
retry cleanup to manufacture certainty. Free results regardless of other faults.
Whether a merely initialized/failed-connect mysqli object can be positively
released must be qualified on each PHP/driver pair, not guessed from flags.
Unknown release remains closure-unknown; repeated cleanup returns cached truth.

Bootstrap does no application DML, so failure is unavailable even if its owned
session settings may have applied. Once an admitted application mutation has
been submitted, the existing write_uncertain rule still governs unknown
completion/cleanup. No reconnect/replay/rebase/compensation is introduced.

## Compatibility matrix and non-vacuous acceptance

No native row is qualified by this design. Keep the two planned rows distinct:
WordPress6.0/PHP7.4 and the recorded7.1.3/PHP8.5.12, with their genuine pinned
source/driver/setup/option/autoload/cache behaviors and MariaDB10.6.23 target.
Public6.0 source reading is not an installed source pin or runtime proof. Missing
core/PHP fixtures block that row; no acquisition/installation runs now. Explicit
partial current-profile evidence may be recorded later without claiming the
matrix complete. Final production wiring still requires both declared-floor
and actual deployment-profile acceptance, plus installed hook/transport evidence.

Next isolated foundation must exercise the actual dormant orchestration with
fixed process-isolated API/IO doubles, no real extensions/sockets/storage. There
is no production function_exists-driven backend choice or runtime test switch.
Reflection may supply explicit isolated substitutes only; it cannot stand in
for native source/session qualification. Required acceptance:

- A full positive setup transcript reaches private active eligibility only
  after all source/options/connect/helper/readback/schema/seal facts match.
  Existing owner positives remain; default-unavailable-only tests cannot pass.
- Bad source/config/domain, alias, `all`/mode hook or option capability causes
  zero connection attempts and zero unknown callback calls; no protected read.
- Object-only parent initialization must select mysqli on the minimum-core
  model while allocating/opening nothing. Legacy selection, repeat initialization
  or removal of the final connect refusal must assertion-fail those IO counters.
- Allocation/connect false/throw/partial success, silent helper failure, wrong
  selected DB/charset/mode/session/schema or over-budget result never publishes
  ready/active. Independent driver/result/callback cleanup and external counters
  remain observable after returned errors; no fake never-opened success.
- Query/execute reject during every pre-active phase; there is no arbitrary SQL
  or handle-returning public setup entry. Reentry/reuse/second connect and
  post-activation bootstrap calls fail without resetting latches or resealing.
- Guard-removal controls must assertion-kill: attempt latch, source/receiver,
  object-only initialization/refusal, setup-hook admission, final readback-before-
  active, private step restriction and partial-native cleanup. Restore hashes
  and rerun clean suites afterward.
- Separate later owned native proof must establish genuine initialization,
  `%s` escaping/charset, limits, helper effects/physical attempts and partial
  release, then actual adapter CAS/creator/hook/cache/error/no-replay behavior.
  Query-filter counters alone do not count direct setup calls or physical replay.

Keep implementation cohesive; use actual responsibilities/line guards rather
than preselecting file splits, a generic connector framework or consent ledger.
No public auth/form/facade/classifier/legacy behavior changes in this stage.

## Plan progress, validation scope and exact next gate

This design resolves the missing first-connect/setup-versus-admission contract;
it does not implement or unblock physical qualification. Current guards and
public factories remain closed. Historical whole07/07A/13/FULL/toolkit rows,
actual lost-update repair, release and deployment remain pending.

Actual validation of unchanged implementation: related SourcePolicy|
AdminPageCompoundRequestId suites429 tests/1744 assertions, randomized
seed20261028, no failures/skips; structure143 runtime/210 support/179 test files,
177 class names passes. Scoped relative links, diff hygiene, non-document
baseline identity and non-target clean-state checks pass. No native/manual
proof or minimum-core execution was run. Existing tests do not execute the
proposed initializer; they establish preserved current contracts only.

Source-scoped design review checked the state/dispatch boundary, exact owned
allocation, setup-source/hook closure, readback-before-active, partial cleanup,
error privacy and matrix limits. The initial draft's omitted minimum-core
driver initialization was corrected to genuine object-only parent construction
under the final refusal; no core-private flag stamping is selected. Setup `all`/
mode-hook tracking is a required future guard, not inferred from the current
fingerprint. No further design inconsistency was identified. No UI/UX workflow
or whole-plugin release/security completion is claimed by this scoped review.

Publication: design `c9f94155cd96217571d3ccd2f64ae6c386a7ff82` passed
[exact-head CI40676746866](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40676746866).
Actual PHP7.4/8.3 job logs each1263 tests/5709 assertions, no failures/skips;
Node16/16, all configured gates and zero-advisory Composer/npm audits pass.
Hosted suites validate unchanged isolated code, not the proposed initializer,
genuine minimum-core libraries, native setup, authentication or site behavior.

Recommended next gated slice: **repository-only Source-Policy Bounded Initial
Native Construction and Pre-Qualification Bootstrap Safety Foundation**,
isolated API/IO tests only. Implement the one-shot private orchestration,
setup-source/hook admission and partial cleanup while public factories, actual
authentication/storage/native execution and production wiring stay disabled.
Then resume the owned native qualification only under fresh operational scope,
with source/runtime matrix prerequisites explicit rather than assumed.
