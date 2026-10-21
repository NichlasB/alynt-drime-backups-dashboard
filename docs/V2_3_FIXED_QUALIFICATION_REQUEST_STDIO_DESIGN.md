# V2.3 Fixed Qualification Worker Request-Entry and Private Stdio Integration Design

Design successor: [selected COMMIT response-loss / uncertain observation integration](V2_3_FIXED_QUALIFICATION_SELECTED_LOSS_INTEGRATION_DESIGN.md).
Following the separately approved successful no-fault proof, this design selects
a distinct fixed selected worker/grammar and false-root actual observation path,
not a selected mode in the proven no-fault runner. Exact reply-loss accounting,
unchanged guards/clocks/erasure and independent cleanup remain mandatory. It is
design-only; no native experiment, suppression or production wiring is enabled.
Next gate is the socket-free selected qualification observation/transcript foundation.

2026-10-21. Approved **repository-only, design-only**. No executable change,
WordPress bootstrap/authentication, storage/SQL, fixture, process/pipe/socket,
native transaction or response-loss experiment. Production wiring, grants,
activation and rollback remain disabled. No package/release/tag/deploy.
Uploader0.5.25/dashboard0.1.77 unchanged; this is not a release candidate.

## Ownership, evidence and source-scoped boundary

Dashboard owns this design, its implementation-plan entry and the predecessor
[qualification provenance design](V2_3_REAL_UNCERTAIN_COMMIT_QUALIFICATION_PROVENANCE_DESIGN.md)
successor pointer. Uploader remains read-only at
`f339a459622082012bb8203587b5eeb643adca5b`; dashboard starts
`28dbdfd95ed8898cbf26986c07e15adba136c731`, toolkit
`7a03003e8e518385d8bd6a1d3c387d269a33c149`, all clean/synced before work.
Explicit excluded coherent review bases: uploader
`9d3c9d107331f5a7074cc58ac4d91ac6ab91267b`, dashboard
`4a84f89fbc28c9c4f7cab63a2d22e0e78c264a49`, covering the observation family,
not only the latest docs/verification tail. Older root/guard/stdio/loss sources
below are directly reviewed dependencies, not a claim to review all history.

Source-only OptionB dashboard restore point before edits,13212 files:
`alynt-drime-backups-dashboard-20261021-111236.zip` and matching `.txt` sidecar,
under `Toolkits/toolkit-snapshots/alynt-drime-backups-dashboard/`; SHA256
`FCF127CD27901FA968B5F45C16EE3B3A07AE7E6A004BF9D814AD7DFFED63445F`.
No checkpoint branch/stash/restore or site/database backup. Master/toolkit/repo
rules and Site Operations/prospective Plugin Tester profile read. No site is
operated on; no target/Novamira/credential/service/database check is applicable.

Uploader owner `docs/V2_3_QUALIFICATION_WORKER_PRIVATE_TRANSCRIPT_FOUNDATION.md`
establishes isolated extraction/framing only. Its worker entry is unconditionally
closed; positive tests seed its owned instance and mirror root-finally state.
The actual qualifier is called, but there is no genuine authenticated root,
native provenance, OS EOF or physical cleanup proof. Keep that limit intact.
Earlier unchanged-final-class selected witness170/2 and no-fault242/2 remain
historical evidence, not results of this design or of the instrumented composition.

## Source trace and concrete integration gaps

| Inspected uploader source | Required consequence |
|---|---|
| `tests/Support/QualificationObservationWorker.php` | Existing isolated worker stays closed, including its private entry. Do not add a hidden native branch or make a synthetic proof flag true. |
| `tests/Support/QualificationObservationOwner.php` and `QualificationSourceManifest.php` | Final proof composition reuses actual root/guard/native/cleanup bodies and one private qualifier alias/wrapper; complete fixed manifest must pass before construction/entry. |
| `includes/trait-registration-admission-root.php` | Fixed private retained entry invokes one original root. Small issuance precedes protected reads; fresh construction creates a separate command nonce; root finally erases original state. |
| `includes/trait-real-closed-writer-core-guard.php` | Genuine core identity/capability semantics surround nonce verification, with exact original cookie/actor/blog/db and monotonic/UTC budgets. Nonce is not authorization. |
| `includes/trait-registration-admission-qualification.php` | Actual result is observed inside the original call after finish(), before final erasure; spent=true alone cannot establish invocation or a returned array. |
| `scripts/tests/registration-join-proof-fixtures.php` | Existing join runner constructs a different owner, so cannot be reused to silently replace the worker-owned instance. Callback assertion errors must survive the root's broad refusal catch via independent sticky evidence. |
| `scripts/tests/observation-private-channel.php`, `selected-native-channel.php` and `selected-native-control.mjs` | Old v1/v2 protocols have exact grammars and do not carry qualifier evidence. Pair arrives only after writer-role binding inside the original root; it is not available at isolated-worker construction. |
| `scripts/tests/selected-native-process.mjs` | Private child exit/close/pipe settlement and safe diagnostics remain separate from parsed FINAL; no arbitrary worker file or exported raw stdout. |

## Decision: separate fixed proof worker, one genuine retained request

Future implementation uses a separate final, proof-only native worker with private
constructor, fixed owner construction and fixed private request entry. Keep the
existing isolated worker/grammar unchanged and unconditionally closed. Do not
subclass either final owner or add an operation/profile/provider port to production.
This design does not implement, name as existing, or invoke the future worker.

The fixed CLI program owns that worker, one invocation and inherited private
stdio. No caller-supplied owner/class/callable, connection, origin, outcome, clock,
proposal or qualifier receipt. A private compiled bridge may use Reflection for
the **fixed** `admit_retained` method on its own exact pinned composition; never a
method name/object from argv, JSON, a callback or a public run/getter. No reflected
qualifier replay. Only the original root calls the actual qualifier alias/wrapper.

Require read-only declaration/source preflight before genuine WordPress bootstrap
or owned fixture creation, after resolving only the approved target/configuration.
If the manifest cannot be evaluated without executing protected dependencies,
stop and resolve that boundary rather than silently moving verification later.
The worker's source gate runs before owner construction and again immediately
before entry. Retain complete method/state/source comparison and original three
production/composition/projection pins; existing isolated worker/transcript pins
are not repurposed as native pins. Add reviewed fixed native-entry/channel/
supervisor source identities, never accepted alternate hashes supplied by frames.
No eval/source rewriting, unbounded source scan or lowered equivalence requirement.

Future genuine bootstrap must use resolved bundled PHP and owned fixture-only
WordPress tables/users with genuine core functions, fixed query fences and no
site plugins/drop-ins/theme/cron/external HTTP. No source/API doubles in native
qualification. The only request is the original small issuance form created in
that fixed authenticated fixture process, POST and purpose register_closed.
No posted actor or externally supplied expanded blueprint/command. Expected
target details are resolved afresh only after separate native approval.

The original root privately constructs the command/nonce and preserves its exact
return/Throwable/refusal, callback order, source-local pin and real clocks.
Observational nonce hooks return their unchanged input; they must not create or
verify an extra nonce, authenticate on a failure branch, repair state or supply
success. Capture sticky callback assertion failure outside the root too, so a
swallowed assertion cannot turn into expected false or a qualified proof.

## Private late binding without a new authority input

Use a private linear lifecycle: source-verified -> attempt/root-bound -> entering
original root -> writer-pair-bound -> original root returned -> receipt consumed
and erased -> private final offered -> closed/audited, or permanently inconclusive.
Reentry, second call/bind, stale/foreign label, interruption or any missing step
permanently refuses. There is no public setter, rearm, resumed request or retry.

Attempt/root labels belong to the supervisor; the worker privately retains them
before entry. The pair remains absent until the actual pre-BEGIN writer-role
handoff at nonce verification7. Capture the actual root-local pin and three distinct
auth/capture/writer role identities there, after the capture session has closed.
Accept exactly one supervisor pair acknowledgement, bound to that writer/root and
attempt; verify unchanged pin/role/phase/counts again at verification8. Never
prefill a synthetic pair, substitute another owner, or clear source slots to pass.
No channel callback executes while a row-lock transaction is held.

The separate native worker does not call the isolated codec with missing pair
or loosen its three-label constructor. Pair is merely a correlation label for an
observed owned connection; it cannot create an origin, prove a COMMIT, authorize
a read/write or enter the source classifier. Raw pin/nonce/cookie/session secrets
stay process-local. Role IDs are private bounded protocol facts, not support output.

## Reserved native-proof protocol (design only)

Reserve `alynt.qualification-observation.native.v1`, version1, fixed no-fault
program/profile `qualification_no_fault`. It is a distinct future grammar, not an
extension of old observation-v1/selected-v2 or isolated-v1. The discriminator is
not a request-selected mode: other profiles must refuse, not enable response loss.
The no-fault program must not import a loss-capable runner as a hidden alternative.

Common ordered fields: `domain,v,kind,seq,attempt`. Append only the fields below,
in this order. Direction-specific sequence is1/2/3; exactly six frames on success.

| Direction / stage | Kind / seq | Additional exact fields |
|---|---|---|
| Supervisor -> worker, bootstrap | boot /1 | profile |
| Worker -> supervisor, source gate accepted | root_request /1 | source |
| Supervisor -> worker, one root label | root /2 | root |
| Worker -> supervisor, actual writer handoff | bind /2 | root,auth,capture,writer,remaining_ns |
| Supervisor -> worker, one owned pair | bound /3 | root,writer,pair |
| Worker -> supervisor, after original return/erase | final /3 | root,pair,profile,source,root_returned,pre_guards,post_guards,protected_reads,writes,original_slots_empty,observation |

`source` is an exact ordered map with keys production,composition,projection,
isolated_worker,isolated_transcript,native_worker,native_channel,native_supervisor;
each value is64-lowercase-hex. The first five preserve the existing identities;
three new values require reviewed implementation before any IPC. Compiled expected
identities must be checked locally, not trusted because the worker echoes them.
`observation` is the exact existing9-field bounded receipt containing the
actual13-field qualifier result, never a second classifier or external receipt
import. Missing/false/throw/repeated observation cannot produce a success FINAL.
Root result/counters/empty-slot predicates must come from this actual instance,
not asserted constants or derived from phase/spent. Historical outcome and actual
client-release boolean are copied without promotion or repair.

Future pure fixtures must fix all field names/order/types/enums and source-map
entries before IPC implementation: three distinct64-lowercase-hex labels,
positive distinct role IDs, bounded canonical decimal remaining_ns, exact boolean
root result, bounded integer counters and original receipt/result schema. One
ASCII JSON line/LF per frame, no escapes/duplicates/extra/missing keys, depth8,
4096-byte frame,32768 aggregate bytes across both directions, six-frame cap.
Reject malformed UTF-8/non-ASCII, controls, truncation, trailing bytes, reordering,
wrong direction/version/domain/profile, unexpected callbacks and replay. A
partial/invalid stream is inconclusive, never repaired by a later valid frame.

Do not put native_proven/authenticated/closure_proven=true in worker messages.
The qualifier's own authority/storage/dispatch/execution flags remain false even
when real core guards were exercised. Supervisor may report narrowly qualified
native evidence only after every independent conjunct below; parsing is not proof
of sender identity, direct original-final-class execution, durability or authority.

## Original time budgets and independent completion

Keep the original ten-second acceptance budget and UTC window. Root coordination
does not replace/reset source started_ns; supervisor's own real monotonic budget
begins when releasing its root acknowledgement, and must not pretend its tick is
the worker's tick. Bind/ack and recheck remain within the first4s of the actual
root, ack within1s, leaving at least6s of original acceptance budget. No virtual
clock, fresh deadline, larger SQL/socket timeout or automatic second attempt to
make instrumentation pass. Existing bounded IO/child settlement primitives may
be reused only after their native runtime profile is verified compatible.

Final must arrive within the original acceptance envelope; qualification and
handoff overhead count. Closed-source-slot inspection happens after original root
finally and before observer extraction. Consume once, clear receipt before
validation/serialization, discard private references on every exit; never invoke
native close/clear original slots merely to manufacture the observation predicate.

Supervisor closes stdin once after exact FINAL, drains stdout to real EOF with
no trailing byte, checks bounded stderr/diagnostics, successful child exit AND
close, settled writes and destroyed pipes. An offered FINAL alone is not EOF,
process closure or physical cleanup. Original active acceptance and bounded
post-attempt cleanup budgets remain separate; cleanup time does not extend or
retroactively validate an expired request. Unresolved resources stay unresolved.

Only then reconcile independent direct-backend proposal/other-row/lock/session
evidence, owned fixture/user removal and complete application-state preservation.
Resolve the full baseline table set (historically77, not a new count here).
No cleanup DROP/fixture deletion before owned sessions/locks are safely resolved;
no claiming released=true merely because an external session later disappeared.
Support output contains only allowlisted counts/verdicts/fixed failure location,
not protocol lines, labels, source graphs, SQL, paths, cookies or credentials.

## Future falsifiable acceptance and gates

| Layer / future case | Required evidence; not executed by this design |
|---|---|
| Pure/API before IPC | Fixed owner/entry, positive actual qualifier returns, one-shot extraction/clear, all late-binding/source/protocol/type/size/EOF failures. Stubbed roots are explicitly isolated, never native proof. |
| Genuine malformed/denied/stale/wrong nonce-domain | Zero protected reads/writes and zero qualifier entries where original early guard denies; verify both small-issuance and constructed-command intent domains without extra auth calls. |
| Genuine no-fault composition | Same owned instance/root, expected8 pre/2 post nonce verifications (ten total, not ten standalone authentication checks),40 protected projections/one DML attempt, actual true root result and exactly one actual committed closed-array observation/current acceptance true; actual client-release boolean retained. |
| Always-unavailable or fabricated observer | No-fault positive must fail if entry/return is missing, qualifier always false, source pin/slots changed, output fabricated or original erase bypassed. Descriptor controls alone are not actual semantic mutation kills. |
| Lifecycle/transport faults | Failed callback outside root, foreign/second pair, stale original clock, partial final, false EOF, nonzero exit, unsettled output or unresolved owned child/session/lock independently prevent proof. |
| Physical no-fault audit | Original proposal exact, other-nine ledger rows unchanged, expected owned sessions absent, ten locks independently available, fixtures/users removed and application checksums unchanged. No write to actual options/cron/users. |
| Later selected uncertainty | Separate approval only after no-fault transparency/cleanup. Original8 pre/0 post guards, genuine client refusal and actual observed uncertain result/current acceptance false; never infer classification from server OK/poisoned phase or synthesize it. No selected attempt authorized here or by no-fault approval. |

Implement/validate pure protocol and fixed-entry contracts before any future
genuine bootstrap/fixture write/IPC. Then require a newly approved exact Plugin
Tester local-only target and owned user/storage/listener/process cleanup scope,
bundled-PHP preflight and initial bundled-mysql baseline per Site Operations.
Existing application state and production wiring stay untouched. Stop on failed
no-fault transparency, unsupported profile, budget miss or unresolved cleanup;
do not proceed to response discard or reinterpret the failure as uncertainty.

## Canonical focused review and executed validation

Feature Light: three dashboard documentation files, Other/design-only (R1
repository change; described native actions remain approval-gated). All nine
categories considered: ownership/source trace, lifecycle/refusal, no new runtime
surface, guard/order/nonce boundaries, finite resources and evidence limits.
Late-pair and wrong-owner join-runner gaps resolved in the design; no code defect
or implementation fix claimed. No unresolved design issue/manual product choice.
Bloat/Structure unnecessary: no PHP/JS/CSS change or new abstraction implemented.
No UI/UX workflow; no new DS3/07A/Database/physical-transaction readiness claim.

Fresh unchanged-uploader isolated checks: qualification125/12735 and Node472,
zero skips/failures; no WordPress/native process/fixture proof. Dashboard PHP
299/1423/two known Windows Sodium skips and WPCS138 pass. Structure/build,
Markdown links, source literals/order and docs-only diff checked at publication.
Actual exact-head dashboard CI counts/links and toolkit docs-only remote/check
evidence are recorded in the toolkit at closeout, not inferred from old runs.

Final focused Feature Security after source/design crosschecks and local QA:
no new input/HTML/SQL/file/auth/CSRF implementation. Design forbids arbitrary
providers/handles, profile-selected loss, extra failure authentication, raw private
export, weakened source pins or receipt-to-authority promotion. No confirmed scoped
security defect/new defect regression handoff; future-layer matrix above remains
a plan, not completed tests. FULL_PRE_RELEASE is reserved for the coherent RC
before packaging/publishing work containing this feature; this is not that suite.

Implementation-plan progress: fixed genuine-entry/private handoff contract is
selected, including the late-pair gap; real native qualification is still unproven.
**Recommended next gated slice: Plugin Tester local-only V2.3 Fixed Qualification
Worker Genuine Request and No-Fault Private Stdio Integration Proof**, including
isolated implementation first and separately confirmed owned disposable users/
storage/listeners/process testing and cleanup. Keep response suppression, actual
application state, production wiring/grants/activation/rollback untouched. This is
the next meaningful owned-native boundary, not another generic DATA-only layer.

## Approved implementation and no-fault proof follow-up — 2026-10-21

The original design above remains historical design-only evidence. Its separately
approved implementation/proof is now owned by uploader
`docs/V2_3_QUALIFICATION_NATIVE_NO_FAULT_PROOF.md`, including failed attempts and
their independently approved diagnostic successors; no failed result was relabeled.
Last repair isolates the full genuine guard prelude in a different process and
owned fixture lifetime rather than raising the4096-frame qualification relay cap.
Prelude266/11 and independent cleanup pass first; one fresh no-fault attempt then
passes with actual original root true,8/2 guards,40/1 IO, actual one-entry/one-return
qualifier, original erasure, exact six-frame private handoff/EOF, three role lifetimes,
native post-COMMIT drain and6/6 byte-equal relay streams. Both independent physical
audits pass with77 application checksums unchanged and owned fixtures removed.
No response suppression or production activation; original five source pins,
all guards and existing budgets/resource limits remain unchanged.

Dashboard pre-doc source snapshot131003/13219 files, matching ZIP/.txt under
toolkit-snapshots, SHA256
`096BA05F09CBA05EA78F6F3AF7DD48659F7B3E2625C5AB5E5301E91ADA20F539`.
Two dashboard docs only; no executable/public contract change. Source-scoped review
and actual publication/CI evidence are in the owning proof/toolkit records. This
does not establish uncertain-COMMIT provenance, direct original-final-class proof,
durability/retry authority or whole-plugin release readiness. Versions unchanged.

**Recommended next gated slice: local-only V2.3 Fixed Qualification Worker Selected
COMMIT Response-Loss and Uncertain Observation Integration Design**, design-only.
No further native experiment, storage write, suppression, wiring, grant, activation,
rollback, package, release or deploy is approved by this completed no-fault proof.
