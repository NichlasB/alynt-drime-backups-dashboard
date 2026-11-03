# Owned ABI Canary Startup-Failure Evidence and Recovery Design

Implementation follow-through: the separately approved repository-only
[safety foundation](SOURCE_POLICY_OWNED_ABI_STARTUP_FAILURE_FOUNDATION.md) records
the actual dormant implementation and isolated tests. The design-stage authority
and test statements below remain historical, not new native authorization.

## Approval, baseline and proof boundary — 2026-11-03

User approves repository-only **design**, validation, commit/push and ordinary
exact-head CI. R1 documentation only: no implementation or classifier wiring,
native/engine operation, native/candidate acquisition/build/import, installation, SQL, installed
site, candidate execution, production wiring, release/tag/package or deployment.
Uploader/toolkit remain untouched. Native allowance stays exhausted at10/10;
this approval does not reset or renew it. Campaign8/10 milestones,static9 unchanged.

Restore/review baseline **6dbd7600dfb35be0342d864bd108e532bedfe0d6** is clean/synced,
equals remote master, and [ordinary CI41701804332](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41701804332)
is reverified successful. Read the repository instructions, owning implementation
plan, [actual final-attempt evidence](SOURCE_POLICY_OWNED_ABI_CANARY_PROFILE.md#final-approved-attempt-compatibility-verified-startup-fails-ceiling-reached--2026-11-03),
fixed controller/contract/workflow and existing isolated tests before this design.

Historical run41701111079 reached the first builtin CLI start/attach command,
returned1 with157 withheld stderr bytes, then completed verified owned cleanup.
Its stderr hash is not recoverable message evidence. The exact cause, whether
the container process ever started, and all syscall qualification remain unknown.
Do not guess the lost message, search a hash dictionary, or use a synthetic
message as if it were that run's captured output.

## Source-scoped discovery

[Owner.run](../scripts/ci/source-policy-owned-abi-canary.py) currently retains phase,
return code, stderr length/hash for completed command failures. It rejects every
nonzero status **or any stderr**, reaps its direct child, and raises the original
bounded refusal; command/deadline/overflow failures can occur earlier. The
[contract](../scripts/manual/source_policy_abi_canary_contract.py) and first-profile
diagnostics are separate, and the outer handler preserves primary failure before
cleanup. No repository consumer turns ADBOWNEDABIPROOF1 diagnostics into grants;
search confirms only the owner emits that format. Do not reuse the separate SQL
minimum-lab packet/parser or change its source cohort/acceptance.

Read the pinned official [Docker CLI start implementation](https://github.com/docker/cli/blob/b8034c0ed70494a90c133461d145cd072d920d7c/cli/command/container/start.go)
through its Contents API. In attach mode it inspects and attaches before calling
ContainerStart, then waits for IO/status; errors can also reflect attachment or
container exit. Thus a submitted `docker start --attach` command is **not proof**
that the daemon start call occurred, nor that status1 is a container exit code.
No particular error-message family is established for the historical failure.

## Selected architecture: observations only, no new executor

The next implementation should extend the existing small DATA contract and
owner, not introduce a generic command runner, arbitrary parser callback,
diagnostic endpoint, workflow selector or alternate execution profile. Keep the
current fixed argv, exact head/source/ownership/pre-start checks, explicit
default AppArmor, canonical UTS and all syscall expectations unchanged.

At the existing startup call site, bind an optional diagnostic descriptor to the
actual owner context/image/CID/ABI/policy. Match the executed argv exactly:
`/usr/bin/docker --host unix:///var/run/docker.sock start --attach <owned-full-CID>`.
The descriptor must not label another argv, CID, command or shell as startup.
It is a correlation check, not authority to dispatch anything. Mark `cli_spawned`
only after Popen succeeds; never emit engine_start_confirmed=true from this fact.

Capture at most the **first** failed startup-command observation while its bounded
buffers still exist. Existing nonzero/stderr, exception, signal, timeout and
overflow rejection rules stay authoritative. A recognized warning, matched
message, status0 with stderr, or empty/unknown diagnostic cannot authorize success,
suppress a refusal, retry, increase a budget or continue to the next ABI.

## Proposed optional packet (not implemented)

Append a non-authoritative `startup_failure` under the existing failure object;
retain old command summaries and outer packet behavior. Reject extra fields,
wrong types (including bool-as-int), invalid bindings and inconsistent lengths.

| Field group | Closed DATA contract |
|---|---|
| Identity | format=ADBOWNEDABISTARTFAIL1; operation=container-start-attach |
| Invariant flags | authority=false; qualification=false; candidate_execution=false; sql=false; production_wiring=false |
| Binding | owner-context SHA256, exact image ID, full CID, ABI enum, builtin/derived selector; copied from the same source-held operation |
| Outcome | spawn-error / cli-exited / timeout / overflow / command-error; cli_spawned boolean; observed CLI return code or null; observed signal or null |
| Streams | observed stdout/stderr byte counts; separate EOF/completeness flags; complete stderr SHA256 or null |
| Analysis | recognized / unclassified / ambiguous / unavailable / invalid; finite ordered reported-layer/topic enums only |
| Cleanup observation | separate optional bounded state projection described below, never a startup/success receipt |

Only actual wait results may supply CLI status/signal. Counts describe observed
bytes, including any last read that triggered overflow, not an invented complete
stream size. No EOF or incomplete/truncated stream means **no complete-stream
hash** and no message classification; preserve existing partial diagnostic facts
without relabeling them complete. No input bytes means unavailable, even when a
historical length/hash is present. Packet validation cannot fabricate evidence.

## Bounded message projection, never raw or generally sanitized text

Prefer a pure byte-to-enum projection over logging a redacted/free-form message.
Do not persist raw stderr/stdout, exception text/tracebacks, arbitrary field names,
environment entries, paths, URLs, policy JSON, quoted identifiers or unknown text.
Retaining a raw string first and redacting it later is not the selected design.

Admit only complete byte input within the existing4KiB startup output cap,
at most16 lines and1KiB per line. Controls/ESC/NUL, invalid encoding, oversized
lines, ambiguous quoting or malformed envelopes produce invalid/unclassified
evidence, not best-effort repaired text. Mask quoted/untrusted identifier/path
spans internally before recognizing topics; never emit the masked string either.
Use fixed linear scans/bounded literals, not caller regexes or backtracking.

Proposed finite vocabulary, to be source-reviewed and frozen in implementation:

- Reported layers: docker-daemon, containerd-shim, oci-runtime, runc, attach.
- Reported topics: permission, missing-path, exec-format, apparmor, seccomp,
  cgroup, namespace, unknown.

These are **message markers**, not root-cause findings. A layer requires a fixed
recognized wrapper/prefix; a topic requires a fixed whole phrase/token outside
masked spans in that envelope. Do not infer a later layer from an earlier one,
match topics in arbitrary opaque/quoted payloads, or classify by hash/byte count.
Conflicting/multiple chains remain ambiguous; unrecognized/version-changed
messages remain unclassified. Catalog additions require fixtures/source review,
not a permissive wildcard. No enum selects a dependency install, relaxed policy,
different runtime/image/ABI, new permissions or automatic recovery operation.

## First failure and cleanup ordering

Preserve the original refusal/exception object and primary outcome. Capture
failure cannot replace it; retain only a fixed unavailable category when evidence
is unsafe or remaining budget is insufficient. Later command failures, profile
reports and cleanup errors cannot overwrite the first startup observation.

For any future state context, **reuse the first already-existing owned cleanup
inspect row**; add no inspection, logs, exec, file/proc read or follow-up command.
Validate full CID, owner labels, expected image and same operation binding before
projecting anything. Foreign/changed/malformed ownership produces unavailable/
refused evidence without exposing the row or changing cleanup's safety decision.

Project only status from the fixed Docker state enum (created/restarting/running/
removing/paused/exited/dead), Running/OOM booleans and an observed bounded exit
code, with basis=first-owned-cleanup-readback-after-failure. Exit code is null
unless a valid exited/dead row supplies it; a created/running row's default zero
is not an observed process exit. Even an exited row does not prove canary execution.
Never copy State.Error, PID/path/configuration/environment or a raw inspect object.
This is a later cleanup-time observation, not the state at CLI submission or
loaded-policy proof. A later running/exited state does not retrospectively turn
the failed command into success. Projection failure must not prevent the existing
safe stop/removal/reconciliation. Keep secondary cleanup issues separate.

## Resource and lifecycle invariants

Keep startup15s/4KiB, aggregate native output16KiB, body control512KiB, cleanup
output512KiB, observation2MiB, current <510s wall-clock reserve check, cleanup30s,
job10min and summary64KiB unchanged. No extra command, reread, timer or cleanup
extension is introduced. Classify at most4KiB using at most16 bounded linear
passes plus constant bookkeeping; implementation tests must measure that bound.
The optional serialized diagnostic is at most4KiB and consumes remaining existing
control/observation allowance; raw stream bytes already counted are not double-
charged as input. Insufficient reserve means unavailable diagnostics, not a new
budget or a masked failure. Existing child reap and independent cleanup remain.

Transient bounded buffers already held by the runner are the only classifier
input. Release references after the first packet is captured; no new raw files,
artifacts, telemetry, cache, logger or credential-bearing child environment.

## Required future isolated acceptance tests (not execution claims)

| Test family | Required discriminating evidence |
|---|---|
| Operation binding | exact argv/owner/CID/ABI/policy positive; altered executable/socket/verb/argv/CID or copied context rejected before attribution |
| Useful classification | genuine-format synthetic recognized messages produce expected enums; unknown/multiple/version-shifted messages remain unclassified/ambiguous |
| No inference | historical157-byte/hash-only record is unavailable; neither status1 nor length/hash identifies cause or successful/absent startup |
| Privacy | secrets, URLs, quoted paths/IDs, environment/policy strings and controls never appear in serialized output or become quoted-span topic matches |
| Bounds/types | exact boundaries, overflow/partial EOF, duplicate/extra fields, malformed numeric/boolean fields and exhausted quota preserve primary failure |
| Owner integration | actual owner functions under isolated fake IO/virtual time capture once; status0+stderr and nonzero+recognized text still fail; successful commands retain their original behavior |
| Cleanup | existing owned-row projection only; foreign row rejected before projection; observation never grants success and projection exceptions do not suppress cleanup |
| Semantic controls | source-backed guard/first-capture/budget/redaction/ownership bypasses must kill relevant assertions, not merely trigger an earlier unrelated startup error |

Label every example synthetic or source-format-derived, not a native transcript.
Use the existing Python/PHP bridge test pattern, patched test namespaces and
virtual clocks/IO; no Docker/compiler/canary child, networking, WordPress or SQL
operation. The existing PHP-to-Python inert test bridge is ordinary test execution,
not a native-lab attempt. Do not add artificial canary success paths or weaken runtime checks.
These are acceptance requirements for the next implementation, **not tests newly
executed by this design**. Current validation reruns only existing isolated checks.

## Recovery decision and next approval

The selected recovery is better evidence plus preserved safe cleanup—not a
speculative fix or retry of the unknown startup failure. Future reported topics
may route repository source review (argv/ELF/profile/version/IO contract), never
an automatic mutation. Root cause remains unestablished until actual qualified
evidence supports it. No whole-plugin review, ABI qualification or release row
is completed by this scoped design.

**Recommended next gated slice: Repository-Only Bounded Startup-Failure Evidence
Safety Foundation and Dormant Harness Integration (Isolated Tests Only; No Native
Rerun).** Implement this small pure projection/first-failure integration in the
existing owner/contract and existing source cohort; validate, review, publish and
verify ordinary exact-head CI. No new executor/profile/dependency or acceptance,
SQL, candidate/production, workflow-dispatch or budget change. The combined slice
avoids unnecessary standalone adapter milestones. Current approval ends at design.

Only after that source-scoped implementation is verified should a separately
explicit, bounded new native allowance be considered, with exact head/profile,
fresh prerequisites and owned cleanup. Nothing in this design grants it or resets
the exhausted10/10 counter. Campaign8/10 milestones,static9 remain unchanged;
do not count this design/docs publication as product completion.

## Design validation and publication

Existing isolated bridge passes1 test/4 assertions, including its21 Python groups;
structure check passes, all3 local design links resolve, and diff whitespace checks
pass. Source-scoped review traces the proposed boundaries against the actual
runner, probe, outer failure handler and cleanup ordering. The diff contains only
this design and its owning plan/profile references; executable source, tests,
workflow, acceptance, quotas and source cohorts are unchanged. No future matrix
test, classifier, recovery implementation or new native proof is claimed.
Commit/push and successful ordinary exact-head CI are required before handoff;
no native workflow is dispatched for this design or its documentation head.
