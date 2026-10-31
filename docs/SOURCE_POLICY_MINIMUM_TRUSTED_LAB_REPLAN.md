# Genuine Minimum-Profile Trusted-Lab Qualification Replan

## Authority and proposed decision — 2026-10-31

Approved repository-only replan at clean/synced restore baseline
`be159d0fdd2cbe3926bdd25805b18bb86948d7b5`. Documentation, existing isolated
QA, commit/push and ordinary exact-head CI only. No artifact acquisition,
candidate/core loading, Docker/native execution, SQL/authentication/fixtures,
site/LocalWP operations, installation, credentials/permissions, runtime/test/
workflow/dependency changes, packaging/release/deploy or production wiring.
Uploader/toolkit remain read-only; no site target or Novamira operation applies.
No whole-plugin readiness rows or product-task counts are refreshed.

**Propose accepting a trusted-lab functional compatibility contract, not
independent loaded-object attestation.** Use the already selected immutable
Linux minimum candidates and genuine native behavior in an approved disposable
lab. Trust the explicitly approved CI host, Docker engine and selected
distributions. Do not claim protection from those trusted components lying.
This replan recommends that contract; the present approval does not accept its
reduced claims or authorize implementation/execution. Current guards and the
[independent-observer deferral](SOURCE_POLICY_LINUX_OBSERVER_DEFERRAL_DECISION.md)
stay in force until the next explicit acceptance.

## Evidence retained and finite candidate

The [ADBLHO2 audit](SOURCE_POLICY_LINUX_PROC_MAPS_POLICY_DESIGN.md#completed-access-audit-existing-authority-unavailable--2026-10-31)
completed at `38c1f0f`, run 41207893634: maps EOF 5,307 bytes / three reads,
then EPERM on the owned fixture's kernel-object open. Cleanup/source/authority
readbacks passed; loaded-object access, mapping consistency and candidate
qualification were not established. No observer retry or privilege workaround.

The [static acquisition proof](SOURCE_POLICY_LINUX_MINIMUM_ARTIFACT_PROOF.md)
verified all 25 selected layer digests/diff IDs, 3,437 core files, 11 source
pins and three selected ELF roots; owned payloads were removed. It is not
loaded/transitive dependency closure, vulnerability clearance or a current
runtime grant. No artifacts remain provisioned by that proof.

Retain the [candidate selection](SOURCE_POLICY_LINUX_MINIMUM_CANDIDATE_FEASIBILITY_DESIGN.md)
and [expected tuple DATA](../scripts/manual/source-policy-linux-minimum-tuple.php):

| Role | Exact selection, still not actual runtime qualification |
| --- | --- |
| PHP donor | `docker.io/library/wordpress@sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4`, Linux/amd64 |
| Runtime expectation | PHP 7.4.33 / mysqlnd 7.4.33, CLI, x86_64/64-bit, non-ZTS; fixed mysqli module, built-in mysqlnd |
| Genuine core | 6.0, commit `cc101b64012b16d087780657a2b828ccd7794a63`, tree `a3bd7a4074a4c95c8af3b969346726273d68e167`; preserve 11 pins |
| Server | `mariadb@sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5`; actual full identity must be `10.11.18-MariaDB-ubu2204` |
| Worker transport | Numeric `127.0.0.1:3306`, TCP, null socket, initial report mode 0 |

The donor's bundled WordPress 6.1.1 is not the core under test. Never run its
entrypoint/FPM or rename its version to 6.0. The chosen newer MariaDB does not
prove minimum database-version coverage, Windows minimum coverage, all PHP 7.4
builds or production connectivity. Legacy PHP is for isolated compatibility
testing, not a secure deployment recommendation. No pins/tags are refreshed here.
In particular, this 10.11.18 lab cannot qualify the unchanged legacy
core-6.0/10.6.23 production row. Its success could close the exact tested
PHP/core/lab case only. Required legacy/intended-profile gaps must be explicitly
reconciled before application wiring/release, never inferred across tuples.

## Guarantee mapping and explicit reduced claims

| Existing requirement | Proposed retained/replacement evidence | Limit requiring explicit acceptance |
| --- | --- | --- |
| Exact artifacts/core/source | Fixed digests/commit/pins, trusted engine image/config readback, parent-controlled read-only source cohort, source hashes before/after | Distribution/engine integrity is trusted; digests are not author authenticity or reproducible-build attestation |
| PHP/driver/runtime identity | Actual version/SAPI/architecture/thread-safety, internal mysqli declarations and mysqlnd client observations, fixed launch/module/config | Observations from the trusted candidate, not independently authenticated against malicious candidate code |
| All actual loaded backing objects/dependencies | Trust the selected immutable image/distribution for loader and dependency selection; retain selected root/static hashes and bounded diagnostics | Drop mandatory independent kernel-object hashes, complete transitive loaded-closure proof and atomic mapping evidence for this lab only |
| Exact loaded-module allowlist | No arbitrary dynamic loading; fixed mysqli only, bounded actual module inventory and before/after agreement | Bundled built-ins belong to the trusted image; do not invent an independently verified expected complete module-origin set |
| Process/run ownership | Parent-created exact CID/labels, engine-reported host PID/start time, worker is the fixed container entrypoint, bounded source/run/phase transcript | Trust engine process attribution; namespace PID is not host PID or independent kernel-start attestation |
| Exact native tuple/admission | Same receiver/context/frame across initial server check, final bootstrap and per-query admission; default legacy cases unchanged | A finite proof-only lab receiver is new internal scope, not a production grant |
| Supported/intended deployment coverage | Record exact passed lab case and remaining rows separately | No transfer to Windows minimum or legacy core-6.0/10.6.23; no release coverage waiver |
| Core source and callbacks | Genuine per-core reflection/include pins, exact stock cache, owned placeholder callbacks and unchanged ambient state | Hash/fingerprint consistency does not qualify unknown callbacks, drop-ins or altered source |
| Report mode/transport/session | Genuine initial/final mode 0, exact TCP/full server/schema/charset/autocommit facts, no reconnect/reissue | No forced mode, suffix stripping, generic server or transport fallback |
| Native correctness | Actual prepare/execute/saved/noop/conflict/failure/uncertainty cases, exact byte probes and assertion-killed controls | A matching row, count or worker success message alone is not proof |
| Independent release | Supervisor-owned control client checks the owned server while worker is held closed and after exit; all-server sessions/transactions | Independent of worker assertion, but server/client/daemon are within the trusted lab, not Byzantine adversaries |
| Resource bounds | Existing command/time/memory/PID/tmpfs/output and owned-stage limits; no increases on refusal | Engine-managed image pull/cache is not an application-level hard byte-capped stream; host/daemon disk policy is explicitly trusted |
| Cleanup | Exact owned CID/process/fixtures/temp removal and independent readbacks; retain fail-on-unknown cleanup | No claim of independently erased shared daemon cache or whole-host teardown; provider disposal is a separate trust assumption |
| Runtime/product behavior | Same SQL/revision/error/uncertainty/ADBC rules, closed public factories, no form/provider wiring | This contract cannot turn a proof receipt into runtime authorization or waive release gates |

This is a deliberate reduction in test-provenance/attestation assurance, not a
claim that the old observer requirement has been met. If independent loaded
closure, kernel PID attribution, adversarial-host protection or independent
hard daemon-download/cache accounting is required, reject this model and keep
the lane deferred. Do not silently implement a weaker substitute.

## Proposed practical lab and observation sequence

Use one exact-head manual workflow on the already selected ephemeral
ubuntu-24.04/x86_64 CI profile with its existing local Docker service only.
No remote Docker context, service installation/reconfiguration, sudo, new
principal/capability or automatic push-triggered native job. Exact repository,
master/head, immutable action/source/image refs and current user approval are
execution authority; environment strings or a receipt alone are not.

[Docker documents digest-addressed pulls](https://docs.docker.com/reference/cli/docker/image/pull/).
Use fixed image digests, never resolve a tag or pick an alternate build after
failure. Freeze expected manifest/config identities from the existing catalog;
record fresh engine image/config/platform readbacks before startup. Pulling is
a separately approved provisioning mutation. Timeout limits the client, not a
claim that daemon transfer/cache has a hard byte bound. Preserve the bounded
static reader/pins and its evidence; do not rewrite it into a custom image
loader or call its 512-MiB stage/2-GiB expanded scan limits Docker cache limits.
Record cache disposition separately, never global prune/shared-image deletion.
Select only fixed non-secret readback fields; never dump full inspect/Env,
process arguments or credential payloads. Later approved disposable database
credentials stay private to owned files/stdin/process memory, never CLI argv,
receipts or logs; no site/production credentials are reused.

Network setup remains the proposed fixed topology: server `network none`,
worker shares that exact owned server CID's namespace, no published ports,
host network or external bridge. [None provides loopback](https://docs.docker.com/engine/network/drivers/none/);
[container mode shares a namespace](https://docs.docker.com/engine/network/#container-networks).
Verify actual engine configuration before authentication; do not claim topology
feasible merely from these docs. Server control-client access is a separate
owned observer channel, not evidence that the PHP worker used a Unix socket.

Bypass donor entrypoint; use fixed PHP CLI, `-n`, fixed mysqli path, empty INI
scan path, controlled environment/CWD and read-only approved source/core mounts.
[PHP documents `-n` and `-d`](https://www.php.net/manual/en/features.commandline.options.php);
do not load mysqlnd.so, optional JSON, donor core or host PHP configuration as
a fallback. Runtime preflight records actual versions/internal driver source,
module/config inventory, mode and zero mysqlnd connection/query counters before
any handle or genuine core construction. Module inventory is bounded to 64
names of at most 64 ASCII bytes, no raw paths; compare start/end. Unknown extra
dynamic module/config/user-code include or changed profile refuses. Trust in
bundled image code is explicit; no extension-file-origin API is invented.

Only after fresh prerequisites pass, load audited genuine 6.0 libraries and the
actual fixed owner in the private worker, not full WordPress/plugin bootstrap.
Exercise genuine mode-0/ambient behavior, original autoload `no` / states
`yes,no`, non-empty known hooks/cache, saved/noop/stale/update/create/remove
conflicts, definite error versus uncertainty, callback reentry, exact quoted/
backslash/NUL/percent/four-byte escape probe and no replay. Preserve genuine
collation/prepare behavior and report actual physical query costs; one core
execute call need not mean one server statement. No ASCII-only shortcut.

Bind approved head/source image/run/CID and both host and namespace identities
explicitly. A fixed worker entrypoint permits engine PID attribution without
an extra shell/exec worker identity guess. Terminals carry a fixed
`evidence_model=trusted-lab-functional-v1`,
`independent_loaded_object_attestation=false`,
`mapping_consistency=not-established`, `runtime_grant=false` and
`production_ready=false`. These are proposed proof labels, not a new public
protocol/provider or receipt-import API. Parent validates exact phase/identity,
required named cases and bounded output, not an arbitrary positive check count.

While the correlated worker is held after owned close, use the separately
supervised fixed control client to verify all-server PROCESSLIST/INNODB_TRX,
including NULL/foreign schemas. Exclude only the exact observer connection;
no schema-only filtering or child-supplied success substitutes. Repeat after
worker exit; remove exact owned tables/accounts/containers/processes/temp data
and verify absence. Actual unexpected state or cleanup uncertainty blocks any
new attempt. Do not promise image-cache erasure or host teardown readback.

## Required implementation seam and unchanged active behavior

The existing [tuple/source design](SOURCE_POLICY_LINUX_MINIMUM_TUPLE_ADMISSION_DESIGN.md)
already identifies the three consumers; do not start another DATA-only layer:

1. [Bootstrap](../includes/traits/trait-source-policy-native-bootstrap.php)
   currently compares actual server to the legacy core/server mapping, then
   separately performs final facts admission.
2. [Connection](../includes/class-source-policy-no-reconnect-connection.php)
   repeats profile/context/report/source checks per operation.
3. [Native profile](../includes/class-source-policy-native-profile.php) must
   continue rejecting the new server on ordinary core-6.0/default calls.

After explicit acceptance, implement the previously designed private exact
receiver-bound lab scope across all three checks in one cohesive slice. Null
remains legacy; wrong/subclass/stale/closed/context-substituted receivers refuse.
No expected-server argument, runtime-learning option, generic registry,
alternate writer, injected ready handle or caller-controlled provider. The
scope is composed only in the fixed source-owned proof worker after trusted-lab
prerequisites; retained after bootstrap inputs clear, revoked before fallible
close. PHP private fields/reflection are not a security boundary against
arbitrary malicious code in that trusted worker. Actual expected DATA alone
does not mint scope. Public factories remain unavailable and real application
composition does not use it. Existing legacy tuples/ADBC1/ADBC2 stay unchanged.

Keep development sources outside plugin bootstrap; runtime includes cannot
require excluded scripts/tests/docs. Old Windows and Linux/socket workers,
receipts, artifact/static readers and deferred observer tooling stay intact.
Minimum support declarations, schema, existing facade/legacy writer and source-
policy POST/nonce/revision behavior are not changed by this replan. The actual
lost-update repair remains unwired and incomplete.

## Fixed limits, tests and finite approval path

Retain bootstrap 20 seconds / 16 credits, connect 2s / read 3s / statement 3s /
lock 1s; native body under 20s and child 25s. PHP container 128 MiB / one CPU /
16 PIDs, memory_limit 64M, 4 MiB scratch and owned 4-KiB donor-volume override;
server no broader than 512 MiB / one CPU / 64 PIDs, 256-MiB DB and 16-MiB tmpfs.
At most two owned containers; controller/control-client processes remain bounded.
Preserve non-root, read-only roots/source mounts, cap-drop ALL, no-new-privileges,
default seccomp and no daemon socket/devices/host-proc inside candidates. No
installation/default-entrypoint fallback if startup requires more authority.
Inspect and override every declared image volume with the fixed owned tmpfs
layout or refuse; read-only root alone is not a no-write/no-persistent-volume
guarantee. No automatic allowance for additional writable paths.
Manual job at most ten minutes; retain per-phase output ceilings (16 KiB per
stream / 64 KiB phase), bounded startup/TERM/KILL and always cleanup. Limits
are proposed feasibility requirements, not measured success or permission to
raise them. Old observer module/read budgets are not repurposed for this route.

The next implementation must include positive receiver construction and real
modeled operation paths plus assertion-killed guards: no scope/default legacy,
wrong source/image/core/tuple/CID/receiver/context/report mode, before-ready and
post-close, mismatched actual server, late profile changes, default core-6.0
rejection of 10.11.18, byte/revision/error/no-reconnect/cleanup and prohibited
attestation/runtime-grant claims. Retain existing tests. Model tests do not
replace genuine native proof, real server failures/retries or physical release.

Finite path, each authorization boundary explicit:

1. **Recommended next gated slice: Accept Trusted-Lab Evidence Contract and
   Repository-Only Minimum-Profile Harness/Receiver Integration.** Acceptance
   must cover the reduced claims above, including engine/cache assumptions.
   Implement fixed worker/controller/manual-workflow composition and the closed
   receiver-bound lab seam, synthetic/source-backed tests, validation/commit/
   push/exact-head ordinary CI. No downloads, candidate execution, database
   operations, credentials or production wiring. No standalone receipt-parser
   or generic fixture/observer milestone; unresolved source closure stops it.
2. Separately approve one exact-head ephemeral-lab acquisition/provisioning,
   fresh connection-free prerequisites and one genuine minimum-profile native
   qualification/owned cleanup attempt, only if prerequisites pass. This must
   explicitly cover owned DB fixtures/accounts and the trusted-engine resource/
   cache boundary. No automatic retry or alternative runtime/profile on failure.
3. After genuine qualification succeeds for the exact accepted lab case,
   reconcile all required supported/intended-profile evidence, including the
   untested legacy/minimum combinations. Unclosed required rows remain a
   product/release gate, not a silent exclusion or unavailable save feature.
   Only when those required gaps close, resume the existing
   [production readiness sequence](SOURCE_POLICY_PRODUCTION_CONNECTION_READINESS_DESIGN.md):
   trusted-context/lazy-provider foundation and owned proof, coherent guarded
   form/facade/legacy switch and genuine WordPress authorization/behavior proof,
   canonical reviews/full pre-release and separately approved release/deploy.

This proposes a finite compatibility route, not proof that selected images
will fit every limit or behave correctly. If the reduced assurance is not
acceptable, retain deferral and report the product gate; do not implement a
workaround. No qualification result, support expansion, production grant or
new product-task credit is created by this design.

## Scoped review, validation and publication

Review maps provenance, loaded-object claims, image/module/process boundaries,
all three admission consumers, no-reconnect/error/mode/resource rules and exact
independent cleanup against actual source. Explicitly separates compatibility
evidence from attestation and subsequent production readiness. Only this replan
and four owning plan/resume records change; no review/readiness row refreshed.

Local PHP 8.5.1: `php vendor/bin/phpunit --do-not-cache-result --filter
'SourcePolicyLinux|SourcePolicyMinimum|SourcePolicyNativeAdapterContract|SourcePolicyTransportProfile'`
passed 535 tests / 14,360 assertions, no failures/skips. These validate existing
contracts and synthetic controls, not this proposed evidence model or a native
minimum-profile result. Structure 149 runtime / 235 PHP support / 228 test files /
225 classes passed. Strict Composer manifest, Composer audit and npm moderate
audit passed, zero known project dependency advisories/vulnerabilities.
Whitespace and 204 owning local documentation targets passed. Runtime,
scripts/tests/workflows, core/image/profile pins, root metadata/assets and
dependencies unchanged against baseline; uploader/toolkit clean/read-only at
`3dfcb15` / `c067eb6`. Full local suite/lint/build not rerun for docs-only work.
No candidate or site operation, artifact download or physical workflow was run.

Replan `c631e81ca62375c47d486d7de35a10a03bd69f39` is pushed;
[ordinary exact-head CI 41222562454](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41222562454)
passed: PHP 7.4 2,448 tests / 23,012 assertions, PHP 8.3 2,448 / 23,017,
no failures/skips; admin 23/23 per matrix with no failures/skips. Configured
lint/build/structure/manifest/dependency audits passed. CI validates unchanged
source contracts, not the proposed trust model, genuine minimum native behavior
or any untested supported tuple. Final docs-only evidence/coverage clarification
receives independent exact-head CI and clean/remote verification at handoff.
No execution authority or evidence-contract acceptance is inferred from CI.
