# Bounded Raw Connected Counter/Single-Close Diagnostic and Owned Cleanup Proof

## Authority and baseline — 2026-10-28

Explicit approval of the [connected counter/release design](SOURCE_POLICY_CONNECTED_COUNTER_RELEASE_DESIGN.md),
then exact target/owned-process confirmation: plugin-tester, local-only, LocalWP
Plugin Tester, http://plugin-tester.local, WordPress at
C:\Users\Captain\Local Sites\plugin-tester\app\public, MariaDB10004/web10005.
Both listeners and the fixed PHP8.5.12/MariaDB10.6.23 metadata were refreshed.
Clean/synced baseline524ac9a2da2fe0ee61bcba372db0e6ac34cf5768. R3 authority is
limited to this private diagnostic and owned process/stdio cleanup, not application
or database writes. Version0.1.77/schema8/PHP7.4/WordPress6.0 stay unchanged.

Novamira guide and complete inventory were checked; plugin-tester.local is present.
No WordPress ability/bootstrap is used: this is a fixed raw mysqlnd reference and
bundled-client observer, not a WordPress operation. The first database access used
the bundled MariaDB client, confirming10.6.23/autocommit1/no transaction, PROCESS
privilege1, zero other all-server sessions/transactions and77 table metadata rows.
This metadata count is not a table-content/checksum preservation proof.

No application table access, candidate SQL, data/schema/fixture write, account,
credential, permission, service/schedule, client/Drime/live, production/form wiring,
owner acceptance, package/release/tag/deployment change. No lifecycle or owner
cohort/positive qualification is run. Existing application state is not used as
test material, and no disposable database fixtures are needed.

## Private implementation and source ownership

- scripts/manual/source-policy-connected-raw-record.php: strict ADCR1 two-frame
  DATA model, fixed15-name vector order below, zero baseline/initialization,
  connected-success/no-query/no-reconnect/result-effect checks and literal close
  acknowledgement/identity/one-call fields. Persistence/open/close statistics are
  exact diagnostic strings, not release or owner qualification. Distinct live
  observations require1/0 sessions before close and0/0 while alive after close;
  clean exit and separate post-exit0/0 are mandatory, within one25s virtual budget.
- source-policy-connected-raw-operation.php in that same manual directory: the
  actual fixed raw operation, shared by native execution and isolated API tests.
  Direct internally allocated handle,2s connect/3s read/local-infile0, fixed
  loopback10004/database/profile, four native snapshots, one literal mysqli_close
  in finally with its latch set first. No wpdb/imported handle, query or invalid
  object errno probe. Primary and cleanup failure kinds remain separate fixed
  classifications; opaque early/late entry failures stay unconfirmed.
- source-policy-connected-raw-worker.php: exact CLI/runtime/mysqli-only gate,
  finite loaded-source/executable/DLL/controller/bridge/shared-transport digest,
  before/after source equality, numeric/enum-only output and redacted errors.
  Its manifest mode loads no WordPress or native session; it never runs proof mode
  as a source check. Source correlation is not distribution authentication.
- source-policy-connected-raw-bridge.php: socket-free private DATA bridge using
  that exact model, no native module or launcher. CLOSE/EXIT tokens are distinct,
  phase/run/source/child-bound, never ADBC1/ADBC-ACK or application authority.
- source-policy-connected-raw-controller.ps1: exactly one raw child, the existing
  bounded Process-object transport, one original25s whole-protocol deadline,
 16KiB stdout/4KiB stderr limits and unchanged all-server count queries. Counts
  include DB=NULL/foreign databases, excluding only the independent observer.
  Actual liveness is checked before/after each live observer call. No foreign
  SQL/user/host payload is read and no foreign process/session is killed.
- source-policy-connected-raw-stdio-audit.ps1: separate approved socket-free
  audit of the actual operation with driver doubles and the actual PowerShell
  transport/bridge. Supplied observer facts are explicitly virtual, never SQL.
- tests/SourcePolicyConnectedRawRecordTest.php and OperationTest.php, with
  tests/support/source-policy-connected-raw-worker.php: DATA tests and real shared
  operation execution under PHP -n API doubles, not fabricated active-owner facts.

All abbreviated filenames above are in scripts/manual except the explicit tests.
No original include, bootstrap, cleanup evidence/cohort/controller, public factory,
reconnect/dispatch/close rule, dependency or CI configuration changes. ADCR1 cannot
parse as existing ADBC1; its completion is explicitly native_qualification:not-run.
No package was built/audited or published; production packaging remains a later gate.

## Non-vacuous isolated validation

New focused suite60 tests/241 assertions; combined new/cleanup/failed-connect
190/701, no failures/skips. The actual operation reaches both holds and samples
four stages; source/ambient/dirty baseline/initialization faults refuse before
later effects. False allocation/options/connect/close, thrown and paired failures,
always-unavailable/closed stubs, extra SQL, drift, wrong/replayed phase tokens,
bad records/counter types, missing live stages and wrong post-exit facts all fail.
Tests count actual double invocations, not only terminal success flags.

Three deliberate controls assertion-failed, then restored exact source hashes:

| Removed protection | Observed assertion failure |
|---|---|
| Live session count guard | Four observation cases erroneously returned CLOSE/EXIT tokens and failed assertFalse |
| Actual close, replaced by ack=true | Actual-operation snapshot assertion detected still-open counters/no close effect |
| Initialization baseline guard | Dirty initialization reached connect; expected invocation counts rejected that ordering |

Restored record SHA256 D904D0D1F5CCD0C6422B07FA5F3FAA95E7DA73FF2253AB70CEADA6683207EC98;
operation EA85457E4D3837DC6D53644959760DEFEDC38052003BE6E4A1108A5BAAE410E3.
No current owner/ADBC1 acceptance file was mutated for these controls.
New real owned socket-free two-process stdio audit10 checks passes; both children
exit/clean up. Existing stdio46/10, source26, genuine error-hook25/6 and pinned
source31 prerequisites freshly pass before the one native attempt. The latter
core-library audits do not bootstrap/authenticate WordPress or open a native session.

## One actual native attempt — complete, not owner qualification

Fresh bundled-client preflight again confirmed PROCESS1 and all-server0/0.
Run e96b5bd1b59b481a870c8ec66d37a57b, raw child32952, profile
php8512-mysqlnd8512-mariadb10623-win64-raw. Frozen source image:
20e1b8710475279ab5c2fe59c3d70b62715c08fb35c95ad3be7abed51ba5c322.
Controller completed in665ms, inside the original25s budget. Exactly one raw
connected attempt; no automatic or manual candidate retry.

Counter order: connect_success,connect_failure,com_query,result_set_queries,
explicit_free_result,implicit_free_result,unbuffered_sets,reconnect,
connection_reused,pconnect_success,active_connections,active_persistent_connections,
explicit_close,implicit_close,disconnect_close. All values are native canonical
decimal strings; no unsigned cast, normalization, clamping or subtraction.

| Actual capture stage | Exact15-counter vector |
|---|---|
| Before allocation | 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0 |
| After initialization | 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0 |
| After successful connection | 1,0,0,0,0,0,0,0,0,1,1,1,0,0,0 |
| After one acknowledged PHP close | 1,0,0,0,0,0,0,0,0,1,18446744073709551615,18446744073709551615,1,0,0 |

Connected frame records allocation1/identity1/ambient0; terminal frame records
identity1/one PHP close invocation1/acknowledgement1/close-error none. The supporting
explicit_close statistic is1. It is not independent instrumentation of C-level
send_close calls or a wire acknowledgement. No implicit/disconnect close or
query/result/reconnect/reuse effect is recorded.

The actual controller required the following observations before advancing:

1. Quiescent all-server baseline0 sessions/0 transactions.
2. Raw child alive with exactly1 other server session/0 transactions before the
   private CLOSE token. Zero while merely unavailable would not pass this phase.
3. Raw child still alive after the PHP close, all-server0/0, before EXIT token.
4. Clean child exit0/no extra stdout/stderr, then separate post-exit all-server0/0.

Bridge completion and clean exit also passed. Controller finally stopped/disposed
only its exact owned Process objects (manifest, raw child, socket-free bridge);
each observer closes its own process in finally. External final reconciliation
again reports0 sessions/0 transactions, zero fixtures created or retained. No
process recovery/forced kill was required on the successful clean-exit path.
Source digest checked inside the operation before/after release and independently
again in connection-free manifest mode after the whole protocol: identical.

This is actual live-child release evidence despite the two wrapped active
statistics and persistent-success1. Those counter values are not sufficient
evidence of a retained server connection in this exact reference. It is consistent
with the design's upstream allocation/refcount-accounting interpretation, but does
not instrument the installed DLL internals, attest its build provenance, prove
universal absence of leaks/pooling, or explain every owner failure predicate.

No application checksum scan was run: scope deliberately touches no application
table. Do not copy the earlier77-checksum claim into this proof. No WP6.0/PHP7.4,
installed wp-config charset, owner positive setup/escaping/schema/seal, actual
WordPress authentication/storage, transaction atomicity or full Database Review
qualification follows. This raw reference is not a saved/conflict result or grant.

## Final preservation QA and publication

Full local PHP8.5.1 suite1758 tests/7767 assertions, no failures, only the two
existing missing-Sodium skips. Node16/16, PHPCS208 files, structure148 runtime/
221 support/191 test files/188 class names, build, strict Composer manifest and
Composer/npm zero-advisory audits pass. New PowerShell scripts parse; scoped PHP
syntax/style, source/diff/ownership review and original acceptance identity pass.
Post-proof combined isolated190/701 passes without another native attempt.

Commands: focused ConnectedRaw and combined ConnectedRaw|Cleanup|
FailedConnectObservation PHPUnit; full --verbose --do-not-cache-result;
npm lint/test:admin/check:structure/build; php composer.phar validate --strict/audit;
npm audit --audit-level=moderate; old source/stdio/error/pinned audits; new
source-policy-connected-raw-stdio-audit.ps1 -ConfirmedOwnedSocketFreeRawAudit;
the sole native source-policy-connected-raw-controller.ps1
-ConfirmedPluginTesterRawConnected; connection-free post-protocol manifest.
All PHPUnit invocations use --do-not-cache-result. Local test/harness formatting
issues were resolved before source freeze/native execution; no assertion weakened.

Uploader/toolkit remain clean at unchanged heads
3dfcb15b5cab8191df7d984251986ead140ded52/
c067eb61883035a1764d830bccffc1e542d3e1dc. Initial published head
c31a7da825b96aee07331da9776a11e582166d28 failed
[exact-head CI40747280957](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40747280957)
on PHP7.4/8.3 during discovery: Cannot redeclare mysqli_get_client_stats in the
new test fixture. No hosted suite counts or green verification are claimed for
that run. Implementation stopped at the CI-failure gate; the approved repair and
subsequent verification are recorded below. Hosted CI validates isolated code, not this manual native proof or
the Windows PowerShell audits. No historical whole-plugin readiness/task-count
refresh, package, release, tag or deployment is authorized by this checkpoint.

## Fixture declaration isolation and CI recovery — repository-only — 2026-10-28

Separate explicit approval covers only test isolation, discovery regressions,
validation/commit/push/exact-head CI. Baselinec31a7da was clean and matched origin.
No site/LocalWP inspection, SQL, native driver/session experiment, proof/manifest
mode, application fixture, credential, schedule, acceptance, wiring or release work.
R1 fixture/test repair, not new native authority. Ordinary PHP -n test children
are socket-free doubles; they are not another Plugin Tester diagnostic attempt.

Root cause: tests/ discovery includes .php support files. PHP registers top-level
functions when a file is included, before its early return executes. The fixture
could pollute a driver-free parent and fatally collide with real mysqli symbols
on the hosted matrix. Only its class/function declarations are now conditional
on the original isolated/no-ABSPATH/no-driver context; the existing early guards,
primitive doubles, failure modes and actual-operation assertions are unchanged.

New SourcePolicyConnectedRawDiscoveryTest checks both a clean PHP -n parent and
a parent containing explicit userland mysqli/adcr canaries. It requires unchanged
function/class registries, constants, relevant globals, output, canary invocation
count and included-file closure: only the fixture, never its executable dependency.
Constants use a trusted serialized self-snapshot (never deserialized), preserving
NAN without the false inequality of comparing NAN directly. No JSON dependency,
native extension load, stubbed-success substitution, skip or threshold relaxation.

Red evidence: final regressions before/without the declaration guard fail with
an explicit symbol-leak assertion (clean parent) and a nested redeclaration fatal
exit255 (existing symbols), matching the hosted collision. After restoring the
guard, discovery2 tests/8 assertions and combined ConnectedRaw/Cleanup/
FailedConnectObservation192/709 pass; focused ConnectedRaw62/249 also passes.
This is discovery/test-integrity evidence,
not another physical close proof. Restored fixture SHA256
4877C2A0AB61D9685B0A25B667A66DA8FDB54B0FA8ECEB62B0B70AF373842823.

Full local PHP8.5.1 suite1760 tests/7775 assertions, no failures, only the two
existing missing-Sodium skips. Node16/16, PHPCS208 files, structure148 runtime/
221 support/192 test files/189 class names, build, strict Composer manifest and
Composer/npm zero-advisory audits pass. Scoped PHP syntax/diff checks and byte
comparison confirm all scripts, includes, acceptance, dependencies and CI config
unchanged from c31a7da. Uploader/toolkit remain clean at the same heads above.
Commands: focused Discovery and combined ConnectedRaw|Cleanup|
FailedConnectObservation PHPUnit; full --verbose --do-not-cache-result;
npm lint/test:admin/check:structure/build; php composer.phar validate --strict/audit;
npm audit --audit-level=moderate; scoped php -l and Git preservation checks.
Every PHPUnit invocation uses --do-not-cache-result.

The changed test fixture is outside the native producer's source image graph.
Historical native665ms/four-stage/live-release evidence remains scoped to its
original image; no native/manifest rerun, new checksum claim or retrospective
owner qualification. Repair checkpoint
c4cca4a5c8e2cc665edafa7a67450590d6ba03c6 passed
[exact-head CI40748998577](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40748998577):
actual PHP7.4/8.3 each1760 tests/7782 assertions, no failures/skips; Node16/16
and all configured gates pass. This evidence-only closeout preserves the tested
fixture/source bytes and receives a separate exact-head check before handoff.

Implementation-plan progress: actual raw connected/once-close/live-release
evidence advances; current owner connected/positive qualification and the actual
source-policy lost-update repair remain open. The next decision is manual evidence
classification, not more native experiments or production activation.
Recommended next gated slice: **Repository-only Versioned Connected Cleanup
Evidence Safety Foundation**, isolated tests only. Introduce a distinct manual
proof contract separating exact counter diagnostics from independently reconciled
release, preserving existing ADBC1 refusals and production rules. No native
execution, owner fact/handle injection or retrospective qualification; later
owner integration/positive proof needs separate explicit approval.
