# V2.3 Same-Root Uncertain Admission Barrier Contention and Failure Qualification Design

Latest isolated successor (2026-10-21): uploader
docs/V2_3_SOCKET_FREE_UNCERTAIN_BARRIER_FOUNDATION.md implements the approved
socket-free terminal/API/byte contracts. No real wait, private native IPC or
physical failure/cleanup proof. Next gate is new terminal-owner genuine
authentication and no-fault integration/owned cleanup, not selected loss or
contention. Production wiring remains disabled; original design permission and
next-gate statements below are historical, not authority for native operations.

2026-10-21. Approved **design-only**, repository records only. No executable,
test, manifest, schema, version or public contract changes. Storage writes,
authentication, WordPress operations, native execution, experiments, response
suppression, production wiring, grants, activation and rollback stay disabled.
This specifies future acceptance; it does not authorize or claim those proofs.

## Boundary and accepted evidence

Excluded design/review baseline: dashboard
`9e89a0caec916c6c4d90afac93134ee3175ad1bf`; uploader source reference (unchanged)
`3fec12034a4c500e65f94ca598b80a2bacf99511`; toolkit evidence baseline
`4bcd8ad8dcf412777a3b7d6f9ffec8484af9ea94`. Review the coherent original-root,
qualification, read-barrier and selected-witness boundary, not just a final docs
commit. Owning parents:

- [Selected-loss design](V2_3_SAME_ROOT_NATIVE_ADAPTER_SELECTED_LOSS_DESIGN.md).
- [Production-wiring readiness](V2_3_CLOSED_ADMISSION_OBSERVATION_PRODUCTION_WIRING_READINESS_DESIGN.md), R1 and the lost-response/contended-barrier row.
- [Implementation plan](IMPLEMENTATION_PLAN.md), same-root selected-loss milestone.
- Uploader `docs/V2_3_NATIVE_QUALIFICATION_SELECTED_LOSS_OWNED_PROOF.md`: one accepted real uncertain writer COMMIT followed by the SAME original root's successful fresh barrier, strict 24 guards/four nonce domains, independent transport/physical cleanup. Its PHP8.5.1/MariaDB10.6.23 profile is not generalized support.

That proof does NOT establish combined uncertain admission plus barrier failure.
Uploader `scripts/tests/registration-contention-{proof,failures,peer}.php` covers
an older standalone observer, sometimes a mutating peer and unreaped-ack physical
primitives. Neither source inspection nor combining those independent receipts
establishes this same-root path. No native rerun is part of this design.

Direct source anchors (all uploader, read-only references):
`includes/trait-registration-native-lifecycle-{root,read,cleanup}.php`,
`includes/trait-registration-observation-{native-barrier,barrier-rows}.php`,
`includes/trait-native-closed-begin-session.php`,
`tests/Support/{NativeQualificationObservation,NativeLifecycleApiCases}.php`,
`scripts/tests/native-qualification-{no-fault-owner.php,grammar.mjs,selected-registry.mjs,no-fault-barrier-drain.mjs}`.

## Keep three independent propositions

1. Historical admission uncertainty: the original sent/unknown-ack capture is
   qualified exactly once by the original alias AFTER owned writer release.
   Preserve `historical_outcome=uncertain`, client-release claim and all existing
   non-authorizing fields; a withheld OK alone does not establish qualification.
2. Fresh observation: only the original root's successful complete native barrier
   and final read guards can return the existing result array. Contention that
   resolves within unchanged budgets may still succeed. Timeout, failed session,
   guard denial, partial sweep or missing acknowledgement cannot.
3. Proof completion: a negative-case supervisor may establish an EXPECTED refusal
   and independently complete cleanup. It must never call that a successful
   barrier, lifecycle closure, server cancellation, safe retry or current grant.

Never replace an original false/throw with an array, inject a supplied root or
qualification, reconstruct erased origin, add an artificial success path, or
let a negative-case receipt pass the existing success FINAL validator.

## Fixed same-root ordering and ownership

Future sequence, not code installed here:

`fresh prerequisites -> original admission/selected witness -> original writer release/erasure -> fresh read guards -> idle barrier binding -> owned blocker ready -> renewed pre-BEGIN guards -> real barrier -> original finally -> independent audits/cleanup`

Retain all guards actually reachable on that path, four separate small/full
mutation/read nonce domains, original actor/blog/session/context and fixed
root/read clocks. Guards9/10 precede writer BEGIN; writer state is erased before
guard11; barrier is idle at guard21 and both21/22 precede its BEGIN. No protected
read before authorization. Do not pad a failed branch to24 guards or manufacture
post-close guards23/24 when DATA finish was never reached.

The prospective blocker is a NEW owned fixture-only session/process, distinct
from auth/capture/writer/barrier and audit. Acquire only the compiled first safety
row lock AFTER admission writer release and before barrier BEGIN; no peer ledger
CAS or options/user write is required. Prove exact fixture marker, server/profile,
thread identities, owned table and actual lock acquisition before READY. Bind
READY to this attempt/root and barrier thread, not a stale peer message. Recheck
authorization and original remaining time after readiness; readiness grants none.
Do not acquire the blocker early and accidentally test admission contention.

An independent read-only monitor must correlate actual requester/blocker wait
identities with the owned table. Sleep duration, a READY message or matching
ledger bytes alone is not evidence of a blocked native barrier. No broad KILL,
arbitrary SQL/provider callback, unknown-thread cleanup or session repair.
Connection-kill, metadata-lock, deadlock, crash and rollback-ack-loss experiments
are outside the initial lock-contention proof and require separate approval.

## Acceptance matrix: do not conflate refusal with success

| Case | Required root/qualification facts | Independent proof obligation |
|---|---|---|
| Fresh committed and selected-uncertain normal barrier controls | Existing genuine original positive result,24 guards/four domains,40 admission and10 barrier projections,1 owned ledger CAS,2 metadata and2 zero-DML checks; all authority flags false | Fresh same-composition no-fault transparency and selected witness, full successful drain and cleanup. An always-false owner fails these controls. |
| Uncertain admission; owned blocker released by rollback before native lock timeout | Same original uncertain qualification, no new admission, complete fresh barrier and existing non-authorizing result, unchanged clocks | Actual owned lock wait, acknowledged peer rollback, normal exact barrier drain and independent absence/lock audits; no blocker DML. Timing/races must refuse rather than waive the wait witness. |
| Uncertain admission; blocker held through actual first-row lock timeout | Original root returns literal false; original uncertain qualifier remains historical only. Expected prefix through guard22,40 admission projections/1 ledger CAS,1 attempted barrier projection,1 metadata and1 pre-BEGIN zero-DML check. No barrier COMMIT, DATA finish or guards23/24 | Native lock-timeout evidence, actual correlated wait, no retry/sweep continuation. Original cleanup attempts definite-ownership rollback, then close. Require independently absent owned sessions, available locks, preserved rows and fixture cleanup. Do not claim the missing second zero-DML check occurred. |
| Guard/context/clock denial before barrier BEGIN | Literal false; exact reached guard prefix, zero barrier projections; historical uncertainty alone permits no fallback | Isolated API trace and semantic controls first. Actual native/auth proof needs its own approved case. No clock reset, nonce minting workaround or extra factory/read. |
| Native throw/false, partial row/session result, unknown BEGIN/COMMIT/rollback acknowledgement, cleanup interruption | Literal false/no success receipt, all private slots consumed/erased, no resend/reconnect/retry | Synthetic/API rejection coverage only initially. Actual connection/fault experiments are NOT inferred from the lock-timeout case; unresolved cleanup blocks proof completion and deletion. |

Exact negative prefix/counters must be checked against the original source and
isolated trace before future native acceptance; they are requirements, not counts
measured here. Reaching `barrier_row` increments attempted reads before execute;
one attempted projection does not imply one successfully fetched row.

`release_native_barrier()` acknowledges success only from `barrier_released`.
After timeout the original `finish()` may acknowledge rollback (`rolled_back`),
but `barrier_receipt` remains false and `settle_native_lifecycle()` must not
promote it. Unknown acknowledgement/failed session can remain `poisoned`.
Null handles or an idle server are not success. Root refusal, client close,
rollback acknowledgement and independent server-resource absence are separate
facts; no requirement to change the original cleanup semantics to pass a test.

## New private negative-case protocol, not a permissive old FINAL

Keep all historical source identities and parsers immutable. The accepted
selected31-file closure `444997f74d1d40207f0f546f9dee10b503b38948dd045cf5592ae9c8ba8d4658`
and no-fault28-file closure `5883cd7caae6a75ecd4e18c7890f4b3731cf91d2ac92a1ae630eca8b10afae7a`
retain their successful-barrier predicates. Add a separate fixed, source-pinned
test-only observation/projection and transcript domain/version for later work;
do not add a caller-selected fault/profile or loosen their drain/claim methods.

Specify exact keys/order/types, guard-prefix enums, terminal cases and canonical
byte examples in the next socket-free foundation before any IPC. Positive and
negative terminal frames must be mutually exclusive and single-consumption.
Bind source digests, attempt/root tokens, original once-only qualifier, unchanged
return kind/value, writer erasure and final slot erasure. Passive observation
must neither invoke extra I/O nor replace the original return/throw, including
when its own observation fails. No handles, origin, proposal, cookie/nonce, SQL,
credentials, row data or arbitrary error strings cross the private channel.

The old four-role/eight-stream registry cannot silently accept a fifth role.
Use a separately owned DIRECT blocker and DIRECT read-only monitor, outside the
four relayed connections, with fixed identities and bounded private stdio
control. Account both database lifetimes and every control pipe separately;
their traffic is not part of the eight-stream transparency claim. No relay
topology expansion or generic fifth-role exception. Unknown/reused/swapped
identities and extra connections disqualify the proof. Native source/profile
verification must include these two fixed helpers and their ownership audit.

Preserve writer-only loss eligibility: upstream request credited first, one exact
11-byte response withheld before ANY target write, no coalesced/pending tail,
other seven original streams equal, no replay/rearm. No second barrier response
discard is permitted. The timeout branch needs a distinct exact error/rollback/
session-validation/QUIT grammar derived from native source; generic SQL/error
acceptance, EOF forgiveness or claiming the old successful COMMIT drain is not
acceptable. If that suffix cannot be validated, report limited facts/incomplete
proof and stop; never call transport failure an expected lock timeout.

Root20s/read10s/writer10s and selected4s with at least6s reserve remain unchanged;
1s ACK, native connect3s/read5s/row-lock2s/metadata3s,24s worker watchdog/28s
containment/90s outer supervisor remain bounds, not server cancellation claims.
Retain4096 frames/pair,2MiB frame/8MiB queue/64MiB attempt,4096-byte canonical
ASCII JSON/LF frame/32768-byte transcript/depth8 caps. Added control traffic and
ownership must fit explicit bounded totals; never silently expand the existing
8 accepted/4 live transport connection budget to admit a blocker. Socket-free
tests must implement the separate direct-helper ownership/cap contract before
any native proposal, without actually opening those sessions or private pipes.

## Independent cleanup and non-vacuous future validation

Before native fault permission: new manifest/composition/positive and negative
API/synthetic coverage, source-backed guard controls, owned containment/cleanup,
fresh genuine guard prelude and SAME composition no-fault transparency. Do not
reuse a historical receipt as a fresh prerequisite. A new passive composition
needs its own no-fault proof before selected loss plus contention is attempted.

Peer release/rollback/exit AND close, owned pumps/listeners/pipes and worker tree
must settle within bounds. Audit known auth/capture/writer/barrier/blocker/monitor
lifetimes individually; auth remains borrowed by the root, never closed by it.
No fixture DROP while any owned session may hold locks. A killed parent, client
null slot, process timeout or claimed cleanup bit does not prove absence.
Unresolved session/lock/process cleanup means failure and preservation/reporting,
not deletion, retry or a new attempt. No cancellation/durable retry guarantee.

Independent owned ledger equals its original retained pre-DML proposal; other
nine rows and borrowed auth facts unchanged. Distinguish the admission's one
fixture CAS from the barrier/blocker's zero DML. Audit actual permitted commands
and available session counters; do not synthesize a second zero-DML receipt on
the failed barrier. Inventory/checksum all existing application tables before
and after each owned lifetime AND after previewed owned-only cleanup. No actual
users/options/cron, production data or existing tables are written/adopted.

Future regression obligations:

- Positive same-root uncertain success and resolved contention; kill an
  always-false owner, missing original qualifier and false-to-array coercion.
- Individually removed/reordered authorization, separate nonce domains, stale or
  malformed intent, expired original clocks, reentry and second invocation:
  assert exact guard/IO prefixes, no protected read/UUID before authorization,
  no write-capable handle and complete erasure; not just an expected false.
- Fake/replayed/wrong-thread wait or READY, early blocker, missing release,
  incorrect timeout code, partial SQL suffix, wrong-role cut, credited-before-
  write confusion, missing cleanup or application delta: reject even when a
  worker prints a success-shaped terminal frame. Assertion-killed semantic
  controls belong to isolated tests first, not unapproved native mutation runs.

Synthetic bytes/virtual clocks/API doubles do not establish genuine WordPress
authentication, real waits, database atomicity/durability, server quiescence or
physical cleanup. This design adds no tests and makes none of those claims.

## Source-scoped review, validation and next gate

Canonical Feature Light then final Feature Security apply to four changed
dashboard records plus direct read-only uploader dependencies listed above.
Light categories1-9: documentation-only, Other/design risk; no UI/API/cron/file
runtime or DB mutation introduced. Three design ambiguities resolved: expected
negative outcome is not successful FINAL; false barrier receipt is not cleanup
failure evidence by itself; blocker readiness is not an actual wait witness.
No executable defect claimed/fixed and no outstanding scoped design decision.
Bloat/Structure and UI/UX unnecessary: zero changed PHP/JS/CSS or UI/copy surface.
No formal DS3 Database/physical review or full pre-release completion claimed.

Final Security considers input/output bounds, SQL ownership, authorization,
four nonce domains, paths/identities, dangerous patterns and regression handoff:
no new runtime surface; no confirmed scoped security defect. Required future
adversarial coverage is recorded above, not represented as implemented proof.
Canonical full pre-release remains required at a coherent RC before packaging.

Local validation and exact-head publication evidence are owned by the toolkit
source-scoped checklist; no historical whole-plugin readiness row is refreshed.
Source-only restore point (not a site/DB backup): dashboard
`alynt-drime-backups-dashboard-20261021-214208.zip` and matching `.txt` under
`C:/Users/Captain/Documents/AI Workflows/Toolkits/toolkit-snapshots/alynt-drime-backups-dashboard`,
13292 files, independently verified SHA256
`CA47DF40FD86DB0602936EA58AEA3E17F5EF48B8D87173935BB011CC8DD6E76E`.
No checkpoint branch, stash or rollback.

Implementation-plan progress: R1 remaining uncertain-admission barrier-failure
acceptance is now designed, not implemented/proven. R2-R7 remain NO-GO.

**Recommended next gated slice: local-only V2.3 Socket-Free Same-Root Uncertain
Barrier Contention and Failure Qualification Safety Foundation**, isolated API
tests/synthetic bytes/virtual clocks only. Implement the new passive terminal
observation, exact negative transcript/accounting and bounded blocker ownership
model; preserve original guards/results/source pins. Separate approval required;
no authentication, IPC/listeners, storage/native execution, response suppression,
production wiring, grants, activation or rollback.

Design-only preservation validation: dashboard PHP suite299 tests/1423 assertions,
two existing Windows Sodium skips, no failures/errors; configured WPCS138 files,
structure guard and build passed. All36 local Markdown links across the four
changed records resolve; whitespace check clean, generated assets unchanged.
Uploader is unchanged and its native/full suites were not rerun. Exact-head
dashboard CI remains a publication gate recorded in the toolkit, not inferred
from these local results.
