# Linux Proc-Maps Completeness and Snapshot-Consistency Policy Design

## Authority, baseline and decision — 2026-10-31

Approved repository-only design at clean/synced restore baseline
`1aa0b7b9642bab638975ce10912a36f5e38c06a6`. Documentation, existing isolated
repository checks, commit/push and ordinary exact-head CI only. No executable,
test, workflow, dependency, artifact/pin, runtime or production-wiring changes.
No new audit dispatch, process-observation experiment, acquisition, candidate
loading, Docker, installation, elevation, capability/permission change,
WordPress/site/database/authentication, package/release/tag or deployment.
No site target or Novamira operation applies; uploader/toolkit remain read-only.

Owning source consumers: [supervisor reader/snapshot/result/cleanup](../scripts/ci/source-policy-linux-observer-audit.py),
[manual exact-head workflow](../.github/workflows/source-policy-linux-observer-audit.yml),
[PHP entry/source tests](../tests/SourcePolicyLinuxObserverAuditTest.php),
[pure diagnostic controls](../tests/support/source_policy_linux_observer_diagnostics_test.py).
They are inspected, not modified by this design.

**Separate EOF-complete access feasibility from atomic mapping consistency.**
Select a future bounded maps stream for the harmless-child access audit only,
explicitly labeled non-atomic. Retain the stronger candidate-observer gate;
neither successful access nor equal sampled cohorts satisfies it. This design
does not implement or activate that policy. Current executable guards remain.
Do not add a privileged helper, custom kernel facility or new principal to
obtain an atomicity claim that has not been shown necessary/proportionate.

## Evidence and source interpretation

The [owning diagnostic result](SOURCE_POLICY_LINUX_MINIMUM_OBSERVER_AUDIT.md#fresh-diagnostic-audit-result--2026-10-31)
binds `60dfbde`, ordinary CI 41199300682 and failed manual run 41199419498:
maps first payload 3,980 bytes / limit 32,768, one-byte EOF probe returned 1.
Cleanup/source/authority readbacks passed. This proves the first read was
partial, not total maps size, cap overflow, map-files denial or feasibility.
Both historical physical failures remain failed; no third attempt occurs here.

[read(2)](https://man7.org/linux/man-pages/man2/read.2.html) distinguishes a
short successful read from zero-byte EOF. The [seq_file interface](https://docs.kernel.org/filesystems/seq_file.html)
describes iterator sessions across reads. Neither promises that a large user
buffer obtains the whole proc stream at once. The observed counts do not
identify this runner's exact kernel implementation or a universal page limit.

The [kernel proc documentation](https://docs.kernel.org/filesystems/proc.html)
warns of races in maps/smaps reads and limits the consistent-output claim to
one read call. PID/start identity and held proc FDs prevent identity confusion,
not remapping. Two equal completed walks can miss transient changes between or
within walks. EOF completeness therefore must not be relabeled atomicity.
[map_files documentation](https://man7.org/linux/man-pages/man5/proc_pid_map_files.5.html)
also documents separate access restrictions. Do not infer their outcome from
the maps failure or assign new capabilities as a workaround.

| Future evidence | Claim permitted | Claim still blocked |
| --- | --- | --- |
| Bounded maps walk reaches EOF and parses | Complete bytes for that walk | Single-instant mapping set |
| Held kernel object matches device/inode and digest | Bytes read from that opened backing object | Immutable process memory or complete loaded closure |
| Two equal enumerated backing cohorts | Sampled cohort agreement | Absence of transient/unobserved mappings |
| Clean existing-authority harmless-child audit | Access feasibility within that exact source/host/run | Candidate/container/native admission or production authority |

## Proposed bounded access-only read policy

Change only the maps acquisition for the separately approved access audit;
keep `read_at`'s existing single-payload/EOF behavior for stat/status and its
strict tests. Do not make a generic fallback reader or silently retry a failed
strict snapshot. A dedicated maps helper owns one FD opened relative to the
held exact proc-directory FD; no child-supplied host path, seek, reopen, restart
or cross-phase/runs transcript assembly. Existing authority is unchanged.

Freeze the proposed ceilings before implementation:

- At most 32,768 accepted payload bytes, plus at most one overflow-detection
  byte; 16 Python read invocations total, including the terminal EOF read.
  Request remaining allowance + 1; refuse immediately on overflow. At the cap,
  request one byte and accept only EOF. No EOF by call 16 means refusal.
- A short nonempty read is accumulated within that one walk, not accepted as
  EOF. Zero bytes establishes stream EOF, not mapping atomicity. Exceptions
  refuse; no retry-to-success or reopening. The call ceiling counts API calls,
  not internal interpreter/kernel retries. Check the shared deadline before
  each call; retain the outer supervisor timeout rather than claiming a
  deadline check interrupts a blocking syscall.
- Buffer once within the byte ceiling; parse only after EOF. Preserve existing
  ASCII/shape/final-newline, 256-row, 64-unique-file, range ordering/non-overlap,
  deleted/anonymous-executable and fixed pseudo-mapping refusals. Incomplete
  final lines, over-budget walks and missing known fixture refuse.
- Preserve held proc FD and PID/parent/start checks before/after observations;
  fixture-first kernel `map_files` open, fstat device/inode and exact known
  4,096-byte fixture hash. No ordinary pathname hash can replace kernel-object
  access. Preserve all enumerated-object 128 MiB / aggregate 256 MiB per-phase
  ceilings, 1 MiB hash reads and digest/deadline checks.
- Keep two fixed observations, same child/authority/source identity, cohort
  agreement check, 25-second parent deadline, 20-second child deadline,
  existing bounded release/TERM/KILL and 45-second outer timeout. No larger
  runtime or resource budget is selected. Cohort agreement remains required
  for this proposed audit, but is not a consistency proof.

No process freeze/ptrace, ioctl workaround, self-report, privileges, new helper
executable or container access is selected. A future stronger candidate policy
would need its own explicit requirements/trust decision, not reuse of this walk.

## Versioned results, ownership and future implementation scope

Propose an explicit new `ADBLHO2` audit result/cleanup format, bound to approved
head, script/workflow/trusted executable source image, run and attempt, exact
PID/start, authority and owned stage. Required fixed scope fields:
`evidence_scope=existing-authority-access-feasibility`,
`read_policy=bounded-eof-stream-v1`, `mapping_consistency=not-established`,
`candidate_qualification=not-established`. Never reinterpret historical ADBLHO1
reports or let a generic `available` boolean satisfy the old candidate gate.
No production consumer, receiver scope, handle, grant or factory is added.

An access-available result requires EOF-complete walks, all existing fixture/
object/hash/cohort/identity/authority checks and confirmed cleanup/source
readback. Actual access denial may complete an access-unavailable audit after
cleanup, but must identify the fixed operation/errno and stream-complete facts.
Unexpected failure, overflow, no EOF, changed identity/source/authority, missing
fixture or uncertain cleanup refuses. Green audit completion never equals
candidate qualification. Report safe byte/call counts and fixed facts only;
no raw maps, paths, streams, credentials or exception text.

Reuse exact owned stage/receipt validation, source-bound launch-gap refusal,
owned-child-only signaling, independent always cleanup and stage-absence
readback. Keep temporary-object membership and flat exact cleanup, never
recursive deletion or global cleanup. Do not claim a second process census or
whole-host teardown from stage absence. Preserve existing ordinary-push refusal
and manual/master/exact-head CI-green admission; no automatic physical run.

Future implementation is confined to the audit harness, its focused synthetic
tests/manual workflow and owning records. Retain strict stat/status/old reader
tests; do not delete an EOF guard test and call that a fix. Add distinct positive
chunked valid maps and exact-cap/EOF cases; partial row, short-read-not-EOF,
cap+1, call exhaustion, deadline, IO denial, missing fixture, PID/source changes,
object mismatch and cleanup failures must refuse at the correct layer. Use
assertion-killed controls for EOF, aggregate/call/deadline bounds, object identity,
path fallback and prohibited atomic/candidate claims. Synthetic evidence must
stay labeled synthetic; never fabricate real expected library hashes.

## Progress, validation and next gate

This source-scoped design resolves the claim mismatch without asserting that
the stronger candidate observer is feasible. Its implementation and physical
access result remain unproved. If actual access is denied, stop at the explicit
trust/product decision; no privilege fallback or further runtime/DATA polishing.
If access succeeds, the atomic/full-closure candidate gate still remains closed;
do not automatically proceed to images, inventory acquisition or candidate PHP.

Source-scoped design review traced the actual reader, snapshot, child, result,
cleanup, workflow and tests. Checked stream versus atomic evidence, independent
backing-object identity, privacy, fixed byte/call/time/row/file bounds, no
fallback, ownership/cancel uncertainty and non-vacuous future controls. Only
this design and the three owning plan/resume records change; no executable,
test, workflow, dependency, artifact/pin or runtime change. No whole-plugin
readiness or physical evidence is refreshed; prior two audit failures stand.

Local PHP 8.5.1 command `php vendor/bin/phpunit --do-not-cache-result --filter
'SourcePolicyLinux|SourcePolicyNativeAdapterContract|SourcePolicyTransportProfile'`
passed 261 tests / 2,859 assertions, no failures/skips. These validate unchanged
contracts, not the proposed stream helper. Structure passed: 149 runtime /
235 PHP support / 228 test files / 225 classes. Strict Composer manifest,
Composer audit and npm moderate audit passed, zero known project dependency
advisories/vulnerabilities. Whitespace and 179 owning local documentation
targets passed. Uploader/toolkit clean/read-only at original `3dfcb15` /
`c067eb6`. Full local suite, lint and build not rerun for this docs-only slice;
No physical audit dispatched.

Design `104081c10732a4f806394c4681ff02c503c34389` is pushed;
[ordinary exact-head CI 41201531139](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41201531139)
passed: PHP 7.4 2,447 tests / 23,007 assertions, PHP 8.3 2,447 / 23,012,
no failures/skips; admin 23/23 per matrix, no failures/skips. Configured lint,
build, structure, manifest and dependency audits passed. This is unchanged
source-contract validation, not implementation of the proposed helper or
physical feasibility. Docs-only evidence closeout and final remote/clean/
ordinary exact-head CI verification do not authorize an audit dispatch.

Recommended next gated slice: **Harness-Only Bounded Proc-Maps Access-Feasibility
Integration and One Existing-Authority Harmless-Child Audit**. Approval should
cover the bounded helper/versioned access-only report, synthetic regressions,
validation/commit/push/exact-head ordinary CI, then one manual owned attempt on
the same ephemeral Linux CI target and exact cleanup. No candidate artifacts/
execution, site/database operations, elevation or production wiring. This is a
single operational gate, not another series of detached DATA milestones.

## Approved access-only integration and one audit — 2026-10-31

User approves that exact combined slice at clean/synced restore baseline
`9eb7ce808c85e770f196a92c5c471271e986353e`. Resolved physical target remains
`NichlasB/alynt-drime-backups-dashboard`, master, existing manual observer
workflow, GitHub-hosted `ubuntu-24.04` Linux/x86_64. Publish/validate first;
dispatch once only at the exact CI-green implementation SHA. Existing host
Python/tools and one owned harmless child/temporary fixtures only. No candidate
images/downloads/loading, native database, WordPress/site work, installation,
elevation, principal/capability/permission changes, release or production wiring.
Uploader/toolkit stay read-only; no whole-plugin readiness evidence is refreshed.

Implementation adds `read_maps_at`, the fixed EOF/32-KiB/16-call/deadline policy,
safe partial-walk/denial facts and fixed ADBLHO2 scope fields/exit validation.
The strict `read_at` and existing parser/diagnostic tests remain unchanged.
Strict and access wrappers share the same existing kernel-object/hash/post-PID
checks to avoid divergent guard copies; no candidate receiver/consumer added.
Two actual audit observations now use only the explicit access wrapper. Child,
stage ownership/receipts, signaling/cleanup, source/authority and all previous
resource ceilings remain. Manual workflow changes only access-only labels;
dispatch/master/head, pinned action, existing tools and always cleanup retained.

New [synthetic walk tests](../tests/support/source_policy_linux_maps_walk_test.py)
cover chunked positive reads, split lines, exact-cap EOF, cap+1, terminal-EOF
call accounting, no restart, deadline, IO/permission errors, fixture/PID/device/
inode/content binding and forbidden candidate/atomic claims. Eleven in-memory
controls are assertion-killed, no harness errors or disk mutations. Prior ten
groups/seven controls and four diagnostic groups/four controls remain intact.
No proc/network/process body runs in these pure/mock checks. Red baseline
failed because new helpers/contracts were absent. An initial local control
selector collision was fixed by a distinct new helper variable name; existing
strict tests and guards were not relaxed. Adjacent PHP: 41 tests / 448
assertions, no failures/skips. Full local PHP 8.5.1: 2,448 / 23,010, no
failures, two existing Sodium signing skips. PHPCS 244 files; admin 23/23,
no failures/skips; structure 149 runtime / 235 PHP support / 228 test files /
225 classes, build, strict manifest, Composer audit and npm moderate audit
passed, no tracked generated-asset changes, zero known project dependency
advisories/vulnerabilities. Whitespace and 185 owning local documentation
targets passed. Scoped security/error/resource/ownership/cleanup/test-integrity
review confirms all limits, exact cleanup and non-admission claims preserved;
protected runtime/manual artifact/pin/dependency and previous pure tests
unchanged, uploader/toolkit clean/read-only. Publication and the one separately
approved audit follow; prior failures are not reinterpreted or rerun.

## Completed access audit: existing authority unavailable — 2026-10-31

Implementation `38c1f0f91bdc4c3c1a7eb066d7d60cb2213ece04` is pushed;
[ordinary exact-head CI 41207779554](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41207779554)
passed before dispatch: PHP 7.4 2,448 tests / 23,012 assertions, PHP 8.3
2,448 / 23,017, no failures/skips; admin 23/23 per matrix, no failures/
skips. Configured lint, build, structure, manifest and dependency audits passed.
Synthetic positives/controls and ordinary CI do not establish physical access.

Exactly one newly approved [manual access audit 41207893634, attempt 1](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41207893634)
ran at that same head on GitHub-hosted Ubuntu 24.04.5 x86_64, image
`20261025.353.1`, existing Python 3.12.3. It completed with a green job and
`ADBLHO2 status=unavailable`, `reason=existing-authority-denied`,
`operation=fixture-mapfile-open`, errno 1 (EPERM),
`phase=access-mapped-file-observation`. Green here means the bounded audit
completed and cleaned up, not successful mapped-object access or admission.

| Actual access evidence | Result |
| --- | --- |
| Bounded maps walk | EOF true, 5,307 payload bytes, three read calls |
| Parsed file-backed entries | 39 rows / 11 unique backing files |
| Known owned fixture | Selected first; kernel map-files open denied |
| Successfully hashed mapped files | 0 |
| Mapping consistency / candidate qualification | Both `not-established` |

Stream acquisition now reaches EOF within the original 32-KiB budget and new
16-call ceiling. The distinct kernel-object permission boundary is observed,
not inferred from the prior short-read failures. No pathname fallback, extra
authority or further attempt followed. No completed startup/final backing
cohort exists; full mapped/dependency closure and candidate/native admission
remain unproved. Prior static artifact byte evidence is not runtime access
proof. This is an exact run/host/source result, not a universal claim
that every Linux host denies access or proof of which capability change would
be sufficient. Do not introduce a privileged helper to chase this result.

Supervisor and child UID/GID 1001/1001, effective capabilities
`0000000000000000`, NoNewPrivs 0; authority readback unchanged. Owned child
PID 2426 / start 20152 reaped; supervisor `cleanup=confirmed`; independent
always-cleanup `owned_stage_absent=true` for exact
`adb-linux-observer-41207893634-1`. Disposable files/root removed, not
recoverable. Both reports bind source image
`eab522376105a232010bd93f10f82fdfec8be0e44d189873bc07174eba4aa192`;
source unchanged. Independent stage absence is not a second process census or
whole-host teardown attestation. Candidate images 0, candidate execution false,
database operations false, privilege changes false; production wiring unchanged.

Only owning records change for evidence closeout; executable/test/workflow
cohort remains at `38c1f0f`. Final docs-only exact-head ordinary CI is publication
verification, not a new physical audit or recovery of candidate qualification.
No whole-plugin readiness, support-matrix change or production grant is claimed.

Closeout checks: adjacent isolated 41 / 448 passed, no failures/skips;
whitespace and 188 owning local documentation targets passed. Diff review
confirms four owning records only, no post-attempt executable/test/workflow
repair. Uploader/toolkit remain clean at `3dfcb15` / `c067eb6`. Final docs-only
remote head, clean/synced state and ordinary exact-head CI are independently
verified at handoff, without another physical run.

Implementation-plan progress: the bounded access question is answered for this
existing-authority CI profile. The independent-observer lane is blocked at a
trust/product boundary, not another reader fix or generic maintenance queue.

Recommended next gated slice: **Repository-Only Linux Minimum-Lab Observer
Boundary and Qualification Deferral Decision**, design-only. Preferred direction:
defer this independent-observer lane under current authority rather than add
privileges, another principal/helper or repeated experiments. Record the exact
product/release impact, unchanged support claims and criteria for revisiting it;
do not weaken the current runtime gate or silently substitute child/path reports.
No new execution, download, site/database operation or production wiring.
