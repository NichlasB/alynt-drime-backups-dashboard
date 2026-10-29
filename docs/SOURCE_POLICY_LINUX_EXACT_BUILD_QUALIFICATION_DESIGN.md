# Source-Policy Linux Exact-Build Admission and Diagnostic Qualification Design

## Authority, baseline and decision — 2026-10-29

Approved repository-only design, validation, commit, push and ordinary exact-head
CI. Clean/synced dashboard baseline
`5ee53606e9538b5efadfbc9827fdf6665fb66199`. This document specifies a future
private admission change; it does not implement or authorize it. No runtime,
tests, scripts, CI, dependency or package metadata changes. Version0.1.77,
schema8, PHP7.4/WordPress6.0 support and current closed public factories remain.
No database/native/manual-workflow execution, WordPress bootstrap, LocalWP/SSH,
authentication, fixtures, installation, credentials/permissions, production
wiring, grants, activation, rollback, package, release, tag or deployment.
Autonomous R2 is not raised by this design of a gated native policy boundary.
Uploader/toolkit remain read-only; no historical release checklist is refreshed.
The present patch is R1 documentation only; the restore point is the pushed
baseline above. Reverting its three records would not change runtime/site state.

Select one exact intended-lab candidate: core7.1.4 ->
**`10.11.18-MariaDB-ubu2204`**, replacing the provisional generic
`10.11.18-MariaDB` row in a separately approved isolated foundation. Do not add
a second accepted string, suffix stripping, prefix comparison, version range,
dynamic discovery, provider registry or caller-selectable profile. This is not
an announcement that the build, deployment or full adapter is qualified.

## Evidence and its limits

The [owned identity audit](SOURCE_POLICY_LINUX_SERVER_IDENTITY_AUDIT.md) at
implementation `d48b8f1243a466f21c01135a3a18dbb06bebf600`,
[run40783576271](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40783576271),
observed both mysqli's API-normalized greeting and SQL VERSION() as
`10.11.18-MariaDB-ubu2204` over a private Unix socket. It established one PHP
connect/SELECT/acknowledged close, independent zero other all-server sessions/
transactions/application schemas, normal server exit and exact owned cleanup.
It initialized fresh vendor system tables; it was not a zero-write operation.
The actual native initializer, adapter, application schema and WordPress did not
run. CLI reporting stayed3; genuine core-generated OFF was not established.

The immutable lab source is the official amd64 image
`mariadb@sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5`,
with PHP/mysqlnd8.5.12 and the11 pinned core7.1.4 files in the
[preflight record](SOURCE_POLICY_LINUX_ENVIRONMENT_PREFLIGHT.md).
That record owns actual CLI/module/server/client digests, not a new download here.
Ubuntu24.04 runner, observed debian-linux-gnu/x86_64 server metadata and ubu2204
server suffix describe different layers; do not infer a new image or OS from one
field. A server version string alone does not authenticate a distribution or
prove source provenance. SQL/greeting agreement is consistency evidence only.
The old generic candidate had isolated double coverage, not a successful native
receipt for this lab; replacing it is an explicit provisional-row migration,
not withdrawal of a qualified Windows/minimum lane.

## Future exact-row contract and affected consumers

| Responsibility | Required future behavior |
|---|---|
| Transport_Profile::server | Keep6.0/7.1.3 ->10.6.23-MariaDB; replace only7.1.4's generic literal with the observed full string; unknown core stays false. |
| Native_Bootstrap | Keep whole-string mysqli_get_server_info equality through that helper, before any initializer command; wrong build closes the sole allocated handle without readiness. |
| Native_Profile::admit | Keep the same helper and whole-string provider-fact equality; matching DATA is not authority or a native grant. |
| Private composition | Keep12 exact frame keys,11 exact fact keys, fixed source bindings, closed factories and no request/profile-selector additions. |
| Transport/charset/reporting | Keep Linux-only core7.1.4 socket tuple, genuine parser/raw DB_HOST agreement, separate socket argument, source-bound helper negotiation and independent utf8mb4/unicode_520 readback. Socket requires observed integer0, never caller mode DATA. |

Existing source locations are includes/class-source-policy-transport-profile.php,
includes/traits/trait-source-policy-native-bootstrap.php and
includes/class-source-policy-native-profile.php. No second build-policy owner
should be introduced. The identity audit's scripts/ci contract deliberately
accepts consistent unqualified versions for observation; do not turn it into
runtime admission or change its historical result into a grant.

Future isolated changes belong primarily to tests/SourcePolicyTransportProfileTest.php,
tests/support/source-policy-transport-cases.php and directly affected initializer/
profile tests. Enumerate literal expected strings independently of the helper;
do not compute all test expectations from the implementation under test. Required
positives reach actual dormant orchestration with doubles on Linux and profile
DATA admission under the existing guarded context. Required negatives include
generic10.11.18-MariaDB, altered/missing suffix, trailing bytes, different patch,
wrong core/transport/platform, mismatched provider facts and socket report3 or
string-valued0. Preserve TCP integer0/3 acceptance under its existing guards.
An always-unavailable owner must fail the positives. Wrong connected identity
must show zero subsequent initializer commands and exact once-only cleanup;
early frame/report refusals must still show zero allocation. Include mismatched
fact rejection even after a successful initializer transcript. Windows must
retain explicit before-allocation socket refusal, not skip or fake Linux.
Assertion-killed controls must exercise removal of the connected identity and
fact identity guards separately, not only mutate the helper's expected literal.
These are future acceptance requirements, not tests executed by this design.

Do not change20s/16-credit initializer bounds,2s connect/3s read/3s statement/1s
lock limits, ready-last ordering, callback/source/reentry guards, strict schema,
CAS uncertainty, no-replay/no-reconnect rules, ADBC1/ADBC2 or error confidentiality.
No actual producer/provider/form migration is bundled with the candidate change.

## Genuine OFF prerequisite, not a reporting accommodation

Keep the socket report0 guard. The identity client's report3 is not adapter
compatibility and is not grounds to relax that guard. Future Linux qualification
must establish the reporting posture from pinned genuine wpdb db_connect(false)
in an explicitly owned context, following the limited construction model in the
[ambient/OFF record](SOURCE_POLICY_AMBIENT_REPORT_OFF_OWNED_PROOF.md): genuine
core creates the ambient handle and selects OFF; no direct mysqli_report toggle,
injected ready/handle state, subclass or caller-supplied mode flag. That ambient
handle is not adopted by the dedicated owner. Preserve receiver/handle/thread,
callback registry and reporting context across operations; independently close
each owned handle. This proves a core-library context, not full application
bootstrap, real user authentication or constructor-failure compatibility.

The prior Windows recipe is a source reference, not a Linux executable profile
or a fresh receipt. A separately approved harness must bind current core7.1.4
sources/parser/helpers, Linux runtime/module image and socket configuration,
account for both handles in all-server reconciliation, and retain independent
owned cleanup. Its environment/fixture authority is not granted here. If genuine
OFF cannot be established inside the existing bounds, stop; do not fabricate
posture, widen budgets, replay a failed attempt or bypass the guard.

## Diagnostic and resource qualification policy

Separate server/container diagnostics from scoped PHP/mysqli diagnostics. The
current initializer converts relevant PHP warnings to refusal and cleanup
warnings to unconfirmed release. Preserve that behavior even for a server build
whose startup warnings are later understood. No catch-and-continue, suppression
or generic benign-warning list is authorized.

The audit retained bounded selected records plus whole-file size/SHA256, not a
complete reviewed raw transcript. Its bootstrap2,137-byte and server3,135-byte
hashes and exact run/source remain in the owning record. No ERROR in those
filtered records is not sufficient for qualification. Required future evidence:

- Re-establish the immutable source/runtime/module/profile image before a run;
  record actual runner/kernel/container restrictions, io_uring posture, resource
  caps and source-bound report context without secrets. Ephemeral hostnames and
  timestamps are context, not new admitted build names or wildcard grant input.
- Review complete bounded startup/shutdown diagnostics in the owned lab, retaining
  support-safe selected records plus size/hash/stage correlation. Preserve64KiB
  per log,32 selected lines and1,024 bytes per selected line as existing ceilings;
  overflow/truncation or missing required evidence fails closure, not silent
  acceptance. Do not publish secrets or arbitrary application logs.
- MAC/WSREP auto-address warnings in this no-network instance and skipped
  hostname/proxy entries under skip-name-resolve are **observed but unqualified**.
  Before qualifying this fixed profile, explain them against its exact vendor
  initialization/configuration and demonstrate required local socket access and
  deterministic ready/normal-stop/cleanup behavior. Do not enable networking/
  Galera, widen account access or change name-resolution settings to silence them.
- io_uring EPERM/kernel.io_uring_disabled is **resource-path qualification debt**,
  not harmless noise. Establish the effective fallback in this same constrained
  image and demonstrate required initializer/CAS/error/cleanup behavior within
  existing deadlines/credits. A successful identity SELECT is insufficient.
  Do not enable kernel facilities, change IO settings or relax limits to green.
- New/changed unexplained diagnostics, ERROR/fatal records, failed readiness,
  OOM, deadline/credit breach, failed release or unresolved owned resources stop
  qualification. Record the failing stage and clean up exact owned resources;
  do not automatically retry. A diagnostic resembling a prior line never
  overrides an operation failure or unknown release.

No new runtime diagnostic parser, relay, isolation framework, platform installer
or toolchain is selected. Diagnostic interpretation belongs to the bounded
qualification record and its source review. If the fixed profile cannot satisfy
these requirements, report a new environment decision gate, not broaden support.

## Evidence staleness and ordered approval gates

1. **Next: repository-only Linux Exact-Build Candidate Safety Foundation.**
   Separately approve the one-literal private candidate migration and non-vacuous
   isolated positive/negative/guard-order regressions above, current-source
   manifest inventory, targeted/full QA, commit/push and ordinary exact-head CI.
   Actual authentication/storage/native execution and wiring remain disabled.
   No diagnostic acceptance engine or native workflow dispatch belongs here.
2. Only after that foundation: separately scope an owned Linux genuine-core/OFF
   and diagnostic/native qualification harness. Resolve exact environment and
   fixture authority, current-source pins, private application-schema ownership,
   resource/session accounting and independent cleanup before executing it.
   Require fresh immutable preflight and genuine OFF prerequisites, then an
   explicitly approved bounded initializer/escape/CAS/conflict/error/release
   proof. The identity audit's privileged empty-instance SELECTs cannot substitute
   for least-privilege application qualification. No production target inferred.
3. Preserve separate Windows/TCP current-source regression and genuine minimum
   PHP7.4/core6.0 qualification gates. Only isolated CI currently covers those
   PHP versions; no native success or full compatibility credit transfers.
   Production provider/form wiring, site state and later release remain separate
   decisions after evidence closure, not automatic next steps.

Changing a helper/fixture later invalidates its loaded-source composition images
and affected manual manifest hashes. Inventory and deliberately rebind exact
source paths/hashes before a fresh proof; never turn an observed hash into its
own authority, copy stale receipts or edit old success counts. Identity/preflight
observations remain historical for their exact images, not candidate acceptance.
Docs-only changes here do not stale runtime bytes or re-execute that evidence.

## This design's review, validation and publication

Source-scoped review traces both server-equality consumers, tuple/parser guards,
report capture/rechecks, warning-to-refusal behavior, isolated positive/cleanup
assertions and evidence limits. No code fix or new guard test is claimed. This
is not a canonical whole-plugin review, Database Review, native experiment,
physical transaction proof, UI review or full release-readiness refresh.
Fresh repository-only QA executed for this design:

- php vendor/bin/phpunit --filter
  'SourcePolicy(TransportProfile|TransportFoundation|ReportingContext|BootstrapFoundation|BootstrapErrorEvidence|LinuxPreflightGate|LinuxIdentityContract)Test'
  --do-not-cache-result:306 tests/946 assertions, no failures/skips.
- php vendor/bin/phpunit --verbose --do-not-cache-result on PHP8.5.1:
  1,986 tests/8,538 assertions, no failures; two existing missing-Sodium signing
  skips (RemoteActionSignerFailureTest and RemoteActionSignerTest), not waived
  native or transport tests.
- npm run lint:PHPCS224 files, no violations; npm run test:admin16/16, no skips;
  npm run check:structure149 runtime/227 support/204 test files/201 class names;
  npm run build successful, generated tracked assets unchanged.
- php composer.phar validate --strict, php composer.phar audit and
  npm audit --audit-level=moderate pass, zero known advisories/vulnerabilities.
- All local links in the three scoped records resolve; diff hygiene and final
  source-scoped policy/security review pass. All non-doc tracked bytes match
  baseline; runtime/test/manual-proof/CI/dependency/package bytes unchanged.
  Uploader3dfcb15 and toolkitc067eb6 remain clean/synced and unmodified.

Exact-head publication evidence follows after push/ordinary CI. No new tests or
mutation controls are credited to this documentation task; the historical native
run remains d48b8f1/run40783576271 only, with no new database/native execution.

Implementation-plan progress: the observed lab identity now has a precise
candidate migration and bounded qualification contract; current-source Linux
native qualification and the production source-policy race repair remain open.
Recommended next gated slice: **Repository-only Linux Exact-Build Candidate
Safety Foundation**, isolated tests only, to align the single dormant candidate
with observed evidence without widening admission or enabling execution.
